<?php

/*
* This file is part of the Moss micro-framework
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Moss;

use Moss\Config\Config;
use Moss\Http\Response\Response;
use Moss\Http\Response\ResponseInterface;
use Moss\Http\Router\RouterException;
use Moss\Kernel\ErrorHandler;
use Moss\Kernel\ExceptionHandler;
use Moss\Container\Container;
use Moss\Dispatcher\Dispatcher;
use Moss\Http\Router\Router;
use Moss\Http\Router\Route;
use Moss\Http\Request\Request;
use Moss\Http\Session\Session;
use Moss\Http\Cookie\Cookie;
use Moss\Security\SecurityException;

class Moss
{
    const SEPARATOR = '::';

    /**
     * @var \Moss\Container\Container
     */
    public $container;

    /**
     * @var \Moss\Config\Config
     */
    public $config;

    /**
     * @var \Moss\Http\Router\Router
     */
    public $router;

    /**
     * @var \Moss\Dispatcher\Dispatcher
     */
    public $dispatcher;

    /**
     * @var \Moss\Http\Session\Session
     */
    public $session;

    /**
     * @var \Moss\Http\Cookie\Cookie
     */
    public $cookie;

    /**
     * @var \Moss\Http\Request\Request
     */
    public $request;

    /**
     * Constructor
     *
     * @param array  $config
     * @param string $mode
     */
    public function __construct($config = array(), $mode = null)
    {
        $this->config = new Config($config, $mode);

// error handling
        $errHandler = new ErrorHandler($this->config->get('framework.error.level'));
        $errHandler->register();

        $excHandler = new ExceptionHandler($this->config->get('framework.error.detail') && isset($_SERVER['REQUEST_METHOD']));
        $excHandler->register();

// components
        $this->container = $this->buildContainer((array) $this->config->get('container'));
        $this->dispatcher = $this->buildDispatcher($this->container, $this->config->get('dispatcher'));
        $this->router = $this->buildRouter((array) $this->config->get('router'));

        $this->session = new Session($this->config->get('framework.session.name'), $this->config->get('framework.session.cacheLimiter'));
        $this->cookie = new Cookie($this->config->get('framework.cookie.domain'), $this->config->get('framework.cookie.path'));
        $this->request = new Request($this->session, $this->cookie);

// registering components
        $this->container
            ->register('config', $this->config)
            ->register('router', $this->router)
            ->register('dispatcher', $this->dispatcher)
            ->register('session', $this->session)
            ->register('cookie', $this->cookie)
            ->register('request', $this->request);
    }

    private function buildContainer(array $config)
    {
        $container = new Container();
        foreach ((array) $config as $name => $component) {
            if (array_key_exists('component', $component) && is_callable($component['component'])) {
                $container->register($name, $component['component'], $component['shared']);
                continue;
            }

            $container->register($name, $component);
        }

        return $container;
    }

    private function buildDispatcher(Container $container, array $config)
    {
        $dispatcher = new Dispatcher($container);
        foreach ((array) $config as $event => $listeners) {
            foreach ($listeners as $listener) {
                $dispatcher->register($event, $listener);
            }
        }

        return $dispatcher;
    }

    private function buildRouter(array $config)
    {
        $router = new Router();
        foreach ((array) $config as $name => $definition) {
            $route = new Route($definition['pattern'], $definition['controller'], $definition['arguments'], $definition['methods']);

            if (array_key_exists('host', $definition)) {
                $route->host($definition['host']);
            }
            if (array_key_exists('schema', $definition)) {
                $route->schema($definition['schema']);
            }

            $router->register($name, $route);
        }

        return $router;
    }

    /**
     * Returns parameter or component from container under set name
     *
     * @param string $name
     *
     * @return mixed
     */
    public function &get($name)
    {
        return $this->container->get($name);
    }

    /**
     * Shitty but handy magic
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->container->get($name);
    }

    /**
     * Registers route
     *
     * @param string          $name
     * @param string          $pattern
     * @param string|callable $controller
     * @param array           $arguments
     * @param array           $methods
     *
     * @return $this
     */
    public function route($name, $pattern, $controller, $arguments = array(), $methods = array())
    {
        $this->router->register($name, new Route($pattern, $controller, $arguments, $methods));

        return $this;
    }

    /**
     * Registers component in container (also variable)
     *
     * @param string $name
     * @param mixed  $definition
     * @param bool   $shared
     *
     * @return $this
     */
    public function component($name, $definition, $shared = false)
    {
        $this->container->register($name, $definition, $shared);

        return $this;
    }

    /**
     * Registers event listener
     *
     * @param string   $event
     * @param callable $definition
     *
     * @return $this
     */
    public function event($event, $definition)
    {
        $this->dispatcher->register($event, $definition);

        return $this;
    }

    /**
     * Fires passed event and returns its response or null if no response passed and received
     *
     * @param string $event
     * @param null   $subject
     * @param null   $message
     *
     * @return ResponseInterface|null
     */
    public function fire($event, $subject = null, $message = null)
    {
        $eventResponse = $this->dispatcher->fire($event, $subject, $message);

        if ($eventResponse instanceof ResponseInterface) {
            return $eventResponse;
        }

        return $subject;
    }

    /**
     * Handles request
     */
    public function run()
    {
        try {
            if ($response = $this->fire('kernel.request')) {
                return $this->fire('kernel.send', $response);
            }

            $action = $this->router->match($this->request);

            if ($response = $this->fire('kernel.route')) {
                return $this->fire('kernel.send', $response);
            }

            if ($response = $this->fire('kernel.controller')) {
                return $this->fire('kernel.send', $response);
            }

            $response = $this->callController($action, $this->request);

            if (!$response) {
                throw new MossException(sprintf('There was no response returned from the controller "%s" handling "%s"', $action, $this->request->uri(true)));
            }

            if (!$response instanceof ResponseInterface) {
                throw new MossException(sprintf('Response returned from "%s" handling "%s" must be instance of ResponseInterface, got "%s"', $action, $this->request->uri(true), is_object($response) ? get_class($response) : gettype($response)));
            }

            $response = $this->fire('kernel.response', $response);

            return $this->fire('kernel.send', $response);
        } catch (SecurityException $e) {
            $response = $this->fire('kernel.403', $e, $this->eventMsg($e));
        } catch (RouterException $e) {
            $response = $this->fire('kernel.404', $e, $this->eventMsg($e));
        } catch (\Exception $e) {
            $response = $this->fire('kernel.500', $e, $this->eventMsg($e));
        }

        if (!empty($e) && !$response instanceof ResponseInterface) {
            throw $e;
        }

        if (!$response instanceof ResponseInterface) {
            throw new MossException(sprintf('Received response is not an instance of ResponseInterface', $this->request->uri(true)));
        }

        return $this->fire('kernel.send', $response);
    }

    private function eventMsg(\Exception $e)
    {
        return sprintf('%s (%s line:%s)', $e->getMessage(), $e->getFile(), $e->getLine());
    }

    /**
     * Calls controller from callable or class
     *
     * @param string|array|callable $controller
     *
     * @return mixed
     * @throws MossException
     */
    private function callController($controller)
    {
        if (is_string($controller) && strpos($controller, self::SEPARATOR) !== false) {
            $controller = explode(self::SEPARATOR, $controller);
        }

        if (is_string($controller)) {
            $response = $this->callFunctionController($controller);
        } elseif (is_array($controller)) {
            $response = $this->callClassController($controller[0], $controller[1]);
        } elseif (is_callable($controller)) {
            $response = $this->callClosureController($controller);
        } else {
            throw new MossException(sprintf('Invalid controller type, got "%s"', gettype($controller)));
        }

        if (is_scalar($response)) {
            return new Response($response);
        }

        return $response;
    }

    private function callFunctionController($function)
    {
        return call_user_func($function, $this);
    }

    private function callClassController($controller, $action)
    {
        if (!class_exists($controller)) {
            throw new MossException(sprintf('Invalid class name or class "%s" does not exists', $controller));
        }

        $instance = new $controller($this);

        if ($res = $this->callMethod($instance, 'before')) {
            return $res;
        }

        if (false === $response = $this->callMethod($instance, $action)) {
            throw new MossException(sprintf('Unable to call action "%s" on controller "%s"', $action, $controller));
        }

        if ($res = $this->callMethod($instance, 'after')) {
            return $res;
        }

        return $response;
    }

    /**
     * Calls controllers method if exists, otherwise returns false
     *
     * @param object $instance
     * @param string $method
     *
     * @return bool|mixed
     */
    private function callMethod($instance, $method)
    {
        if (!method_exists($instance, $method) || !is_callable(array($instance, $method))) {
            return false;
        }

        return call_user_func(array($instance, $method));
    }

    private function callClosureController($closure)
    {
        return $closure($this);
    }
}

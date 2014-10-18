<?php

/*
* This file is part of the Moss micro-framework
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Moss\Kernel;

use Moss\Config\Config;
use Moss\Container\Container;
use Moss\Dispatcher\Dispatcher;
use Moss\Http\Cookie\Cookie;
use Moss\Http\Request\Request;
use Moss\Http\Response\ResponseInterface;
use Moss\Http\Router\Route;
use Moss\Http\Router\Router;
use Moss\Http\Session\Session;

/**
 * Moss app kernel
 *
 * @package Moss Kernel
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class App
{
    const SEPARATOR = '@';

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
        $errHandler = new ErrorHandler($this->config['framework']['error']['level']);
        $errHandler->register();

        $excHandler = new ExceptionHandler($this->config['framework']['error']['detail'] && isset($_SERVER['REQUEST_METHOD']));
        $excHandler->register();

// components
        $this->container = $this->buildContainer((array) $this->config->get('container'));
        $this->dispatcher = $this->buildDispatcher((array) $this->config->get('dispatcher'));
        $this->router = $this->buildRouter((array) $this->config->get('router'));

        $conf = $this->config['framework']['session'];
        $this->session = new Session($conf['name'], $conf['cacheLimiter']);

        $conf = $this->config['framework']['cookie'];
        $this->cookie = new Cookie($conf['domain'], $conf['path'], $conf['http'], $conf['ttl']);

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

    /**
     * Builds container and its definitions
     *
     * @param array $config
     *
     * @return Container
     */
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

    /**
     * Creates dispatcher instance and event listeners
     *
     * @param array $config
     *
     * @return Dispatcher
     */
    private function buildDispatcher(array $config)
    {
        $dispatcher = new Dispatcher($this->container);
        foreach ((array) $config as $event => $listeners) {
            foreach ($listeners as $listener) {
                $dispatcher->register($event, $listener);
            }
        }

        return $dispatcher;
    }

    /**
     * Builds router, routes and registers routes in router
     *
     * @param array $config
     *
     * @return Router
     */
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
    public function listener($event, $definition)
    {
        $this->dispatcher->register($event, $definition);

        return $this;
    }

    /**
     * Fires passed event and returns its response or null if no response passed and received
     *
     * @param string      $event
     * @param null|mixed  $subject
     * @param null|string $message
     *
     * @return mixed
     */
    public function fire($event, $subject = null, $message = null)
    {
        return $this->dispatcher->fire($event, $subject, $message);
    }

    /**
     * Handles request
     */
    public function run()
    {
        try {
            if ($evtResponse = $this->fire('kernel.request')) {
                return $this->send($evtResponse);
            }

            $controller = $this->router->match($this->request);
            if (empty($controller)) {
                throw new AppException('No controller was returned from router');
            }

            if ($evtResponse = $this->fire('kernel.route')) {
                return $this->send($evtResponse);
            }

            if ($evtResponse = $this->fire('kernel.controller')) {
                return $this->send($evtResponse);
            }

            $response = $this->callController($controller);
            if ($evtResponse = $this->fire('kernel.response', $response)) {
                return $this->send($evtResponse);
            }
        } catch (ForbiddenException $e) {
            $response = $this->fire('kernel.403', $e, $this->eventMsg($e));
        } catch (NotFoundException $e) {
            $response = $this->fire('kernel.404', $e, $this->eventMsg($e));
        } catch (\Exception $e) {
            $response = $this->fire('kernel.500', $e, $this->eventMsg($e));
        }

        if (isset($e) && (empty($response) || $response instanceof \Exception)) {
            throw $e;
        }

        return $this->send($response);
    }

    /**
     * Returns response from app
     *
     * @param mixed|ResponseInterface $response
     *
     * @return ResponseInterface
     * @throws AppException
     */
    private function send($response)
    {
        if ($evtResponse = $this->fire('kernel.send', $response)) {
            $response = $evtResponse;
        }

        if (!$response instanceof ResponseInterface) {
            throw new AppException(sprintf('Received response is not an instance of ResponseInterface, got "%s"', $this->getType($response)));
        }

        return $response;
    }

    /**
     * Builds event message from exception
     *
     * @param \Exception $e
     *
     * @return string
     */
    private function eventMsg(\Exception $e)
    {
        return sprintf('%s (%s line:%s)', $e->getMessage(), $e->getFile(), $e->getLine());
    }

    /**
     * Calls controller from callable or class
     *
     * @param string|array|callable $controller
     *
     * @return ResponseInterface
     * @throws AppException
     */
    private function callController($controller)
    {
        if (is_string($controller) && strpos($controller, self::SEPARATOR) !== false) {
            list($controller, $action) = explode(self::SEPARATOR, $controller);
            $response = $this->callClassController($controller, $action);
        } elseif (is_callable($controller)) {
            $response = $this->callCallableController($controller);
        } else {
            throw new AppException(sprintf('Invalid controller type, got "%s"', $this->getType($controller)));
        }

        if (!$response) {
            throw new AppException(sprintf('There was no response returned from the controller handling "%s"', $this->request->uri(true)));
        }

        if (!$response instanceof ResponseInterface) {
            throw new AppException(sprintf('Invalid response returned from handling "%s", expected ResponseInterface, got "%s"', $this->request->uri(true), $this->getType($response)));
        }

        return $response;
    }

    /**
     * Returns variable type or in case of objects, their class
     *
     * @param mixed $var
     *
     * @return string
     */
    private function getType($var)
    {
        return is_object($var) ? get_class($var) : gettype($var);
    }

    /**
     * Calls class method as controller
     *
     * @param string $controller
     * @param string $action
     *
     * @return string|ResponseInterface
     * @throws AppException
     */
    private function callClassController($controller, $action)
    {
        if (!class_exists($controller)) {
            throw new AppException(sprintf('Invalid class name or class "%s" does not exists', $controller));
        }

        $instance = new $controller($this);

        if ($res = $this->callMethod($instance, 'before')) {
            return $res;
        }

        if (false === $response = $this->callMethod($instance, $action)) {
            throw new AppException(sprintf('Unable to call action "%s" on controller "%s"', $action, $controller));
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

    /**
     * Calls function as controller
     *
     * @param string|array|callable $function
     *
     * @return string|ResponseInterface
     */
    private function callCallableController($function)
    {
        return call_user_func($function, $this);
    }

}

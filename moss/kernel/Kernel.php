<?php
namespace moss\kernel;

use moss\container\ContainerInterface;
use moss\router\RouterInterface;
use moss\dispatcher\DispatcherInterface;
use moss\http\request\RequestInterface;
use moss\http\response\ResponseInterface;
use moss\router\RouterException;
use moss\security\SecurityException;

/**
 * Moss Kernel
 *
 * @package Moss Kernel
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Kernel
{

    /** @var RouterInterface */
    protected $router;

    /** @var ContainerInterface */
    protected $container;

    /** @var DispatcherInterface */
    protected $dispatcher;

    protected $pattern;

    /**
     * Constructor
     *
     * @param RouterInterface     $router
     * @param ContainerInterface  $container
     * @param DispatcherInterface $dispatcher
     * @param string              $pattern
     */
    public function __construct(RouterInterface $router, ContainerInterface $container, DispatcherInterface $dispatcher, $pattern = '\{bundle}\controller\{controller}Controller::{action}Action')
    {
        $this->router = & $router;
        $this->container = & $container;
        $this->dispatcher = & $dispatcher;
        $this->pattern = $pattern;
    }

    /**
     * Handles request
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     * @throws \Exception|KernelException
     */
    public function handle(RequestInterface $request)
    {
        try {
            if ($response = $this->fireEvent('kernel.request')) {
                return $this->fireEvent('kernel.send', $response);
            }

            $action = $this->router->match($request);

            if ($response = $this->fireEvent('kernel.route')) {
                return $this->fireEvent('kernel.send', $response);
            }

            if ($response = $this->fireEvent('kernel.controller')) {
                return $this->fireEvent('kernel.send', $response);
            }

            $response = $this->callController($action, $request);

            if (!$response) {
                throw new KernelException(sprintf('There was no response returned from the controller "%s" handling "%s"', $action, $request->uri(true)));
            }

            if(!$response instanceof ResponseInterface) {
                throw new KernelException(sprintf('Response returned from "%s" handling "%s" must be instance of ResponseInterface, got "%s"', $action, $request->uri(true), is_object($response) ? get_class($response) : gettype($response)));
            }

            $response = $this->fireEvent('kernel.response', $response);

            return $this->fireEvent('kernel.send', $response);
        } catch(SecurityException $e) {
            $response = $this->fireEvent('kernel.403', $e, sprintf('%s (%s line:%s)', $e->getMessage(), $e->getFile(), $e->getLine()));
        } catch(RouterException $e) {
            $response = $this->fireEvent('kernel.404', $e, sprintf('%s (%s line:%s)', $e->getMessage(), $e->getFile(), $e->getLine()));
        } catch(\Exception $e) {
            $response = $this->fireEvent('kernel.500', $e, sprintf('%s (%s line:%s)', $e->getMessage(), $e->getFile(), $e->getLine()));
        }

        if (!empty($e) && !$response instanceof ResponseInterface) {
            throw $e;
        }

        if (!$response instanceof ResponseInterface) {
            throw new KernelException(sprintf('Received response is not an instance of ResponseInterface', $request->uri(true)));
        }

        return $this->fireEvent('kernel.send', $response);
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
    protected function fireEvent($event, $subject = null, $message = null)
    {
        $eventResponse = $this->dispatcher->fire($event, $subject, $message);
        if ($eventResponse instanceof ResponseInterface) {
            return $eventResponse;
        }

        return $subject;
    }

    /**
     * Calls controller from callable or class
     *
     * @param string|callable       $controller
     * @param null|RequestInterface $request
     *
     * @return mixed
     * @throws KernelException
     */
    protected function callController($controller, RequestInterface $request = null)
    {
        if (is_callable($controller)) {
            return $controller($this->container, $this->router, $request);
        }

        list($controller, $action) = $this->resolve($controller);

        if (!class_exists($controller)) {
            throw new KernelException(sprintf('Unable to load controller class "%s"', $controller));
        }

        $instance = new $controller($this->container, $this->router, $request);

        if (method_exists($instance, 'before') && is_callable(array($instance, 'before'))) {
            if ($res = $instance->before()) {
                return $res;
            }
        }

        if (!method_exists($instance, $action) || !is_callable(array($instance, $action))) {
            throw new KernelException(sprintf('Unable to call action "%s" on controller "%s"', $action, $controller));
        }

        $response = $instance->$action();

        if (method_exists($instance, 'after') && is_callable(array($instance, 'after'))) {
            if ($res = $instance->after()) {
                return $res;
            }
        }

        return $response;
    }

    /**
     * Resolves controller and action
     *
     * @param string $controller
     *
     * @return array
     * @throws KernelException
     */
    private function resolve($controller)
    {
        if (!is_string($controller)) {
            throw new KernelException('Unable to resolve controller "%s"');
        }

        if (substr_count($controller, ':') < 2) {
            throw new KernelException(sprintf('Invalid controller identifier "%s", must have at least two ":".', $controller));
        }

        preg_match_all('/^(?P<bundle>.*):(?P<controller>[^:]+):(?P<action>[0-9a-z_]+)$/i', $controller, $matches, PREG_SET_ORDER);

        foreach (array('bundle', 'controller', 'action') as $k) {
            if (empty($matches[0][$k])) {
                throw new KernelException(sprintf('Invalid or missing "%s" node in controller identifier "%s"', $k, $controller));
            }
        }

        $r = array(
            '{bundle}' => str_replace(array('.', ':'), '\\', $matches[0]['bundle']),
            '{controller}' => ucfirst($matches[0]['controller']),
            '{action}' => $matches[0]['action']
        );

        list($controller, $action) = explode('::', strtr($this->pattern, $r));

        return array($controller, $action);
    }
}

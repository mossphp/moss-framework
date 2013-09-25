<?php
namespace moss\kernel;

use moss\container\ContainerInterface;
use moss\router\RouterInterface;
use moss\dispatcher\DispatcherInterface;
use moss\http\request\RequestInterface;
use moss\http\response\ResponseInterface;
use moss\kernel\KernelException;
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
    protected $Router;

    /** @var ContainerInterface */
    protected $Container;

    /** @var DispatcherInterface */
    protected $Dispatcher;

    protected $pattern;

    /**
     * Constructor
     *
     * @param RouterInterface     $Router
     * @param ContainerInterface  $Container
     * @param DispatcherInterface $Dispatcher
     * @param string              $pattern
     */
    public function __construct(RouterInterface $Router, ContainerInterface $Container, DispatcherInterface $Dispatcher, $pattern = '\{bundle}\controller\{controller}Controller::{action}Action')
    {
        $this->Router = & $Router;
        $this->Container = & $Container;
        $this->Dispatcher = & $Dispatcher;
        $this->pattern = $pattern;
    }

    /**
     * Handles request
     *
     * @param RequestInterface $Request
     *
     * @return ResponseInterface
     * @throws \Exception|KernelException
     */
    public function handle(RequestInterface $Request)
    {
        try {
            if ($Response = $this->fireEvent('kernel.request')) {
                return $this->fireEvent('kernel.send', $Response);
            }

            $action = $this->Router->match($Request);

            if ($Response = $this->fireEvent('kernel.route')) {
                return $this->fireEvent('kernel.send', $Response);
            }

            if ($Response = $this->fireEvent('kernel.controller')) {
                return $this->fireEvent('kernel.send', $Response);
            }

            $Response = $this->callController($action, $Request);

            if (!$Response || !$Response instanceof ResponseInterface) {
                throw new KernelException(sprintf('There was no response returned from the "%s (%s)" or is not an instance of ResponseInterface', $action, $Request->url(true)));
            }

            $Response = $this->fireEvent('kernel.response', $Response);

            return $this->fireEvent('kernel.send', $Response);
        } catch(SecurityException $e) {
            $Response = $this->fireEvent('kernel.403', null, sprintf('%s (%s line:%s)', $e->getMessage(), $e->getFile(), $e->getLine()));
        }
        catch(RouterException $e) {
            $Response = $this->fireEvent('kernel.404', null, sprintf('%s (%s line:%s)', $e->getMessage(), $e->getFile(), $e->getLine()));
        }
        catch(\Exception $e) {
            $Response = $this->fireEvent('kernel.500', null, sprintf('%s (%s line:%s)', $e->getMessage(), $e->getFile(), $e->getLine()));
        }

        if (!empty($e) && empty($Response)) {
            throw $e;
        }

        if (!$Response instanceof ResponseInterface) {
            throw new KernelException(sprintf('Received response is not an instance of ResponseInterface', $Request->url(true)));
        }

        return $this->fireEvent('kernel.send', $Response);
    }

    /**
     * Fires passed event and returns its response or null if no response passed and received
     *
     * @param string $event
     * @param null   $Response
     * @param null   $message
     *
     * @return ResponseInterface|null
     */
    protected function fireEvent($event, $Response = null, $message = null)
    {
        $EventResponse = $this->Dispatcher->fire($event, $Response, $message);
        if ($EventResponse instanceof ResponseInterface) {
            return $EventResponse;
        }

        return $Response;
    }

    /**
     * Calls controller from callable or class
     *
     * @param string|callable       $controller
     * @param null|RequestInterface $Request
     *
     * @return mixed
     * @throws KernelException
     */
    protected function callController($controller, RequestInterface $Request = null)
    {
        if (is_callable($controller)) {
            return $controller($this->Container, $this->Router, $Request);
        }

        list($controller, $action) = $this->resolve($controller);

        if (!class_exists($controller)) {
            throw new KernelException(sprintf('Unable to load controller class "%s"', $controller));
        }

        if (empty($action)) {
            throw new KernelException(sprintf('Invalid or missing action name in controller identifier "%s"', $controller));
        }

        $Controller = new $controller($this->Container, $this->Router, $Request);

        if (method_exists($Controller, 'before') && is_callable(
                array(
                     $Controller,
                     'before'
                )
            ) && ($Response = $Controller->before())
        ) {
            return $Response;
        }

        if (!method_exists($Controller, $action) || !is_callable(array($Controller, $action))) {
            throw new KernelException(sprintf('Unable to call action "%s" on controller "%s"', $action, $controller));
        }

        $Response = $Controller->$action();

        if (!method_exists($Controller, 'after') && is_callable(array($Controller, 'after'))) {
            $Response = $Controller->after($Response);
        }

        return $Response;
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
            throw new KernelException(sprintf('Invalid controller identifier "%s". Controller identifier should have at least two ":".', $controller));
        }

        preg_match_all('/^(?P<bundle>.*):(?P<controller>[^:]+):(?P<action>[0-9a-z_]+)$/i', $controller, $matches, PREG_SET_ORDER);

        $r = array();
        foreach (array('bundle', 'controller', 'action') as $k) {
            if (empty($matches[0][$k])) {
                throw new KernelException(sprintf('Invalid or missing "%s" node in controller identifier "%s"', $k, $controller));
            }

            $r['{' . $k . '}'] = str_replace(array('.', ':'), '\\', $matches[0][$k]);
        }

        return explode('::', strtr($this->pattern, $r));
    }
}
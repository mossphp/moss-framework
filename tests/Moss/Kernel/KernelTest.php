<?php

/*
* This file is part of the moss-framework package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Moss\Kernel;


use Moss\Http\Response\Response;
use Moss\Http\Router\Route;

function functionController() { return new Response(); }

class TestController
{
    public function action()
    {
        return new Response();
    }
}

class MockKernel extends Kernel {
    public function __construct($container) {
        $this->app = new App($container);
    }
}

class KernelTest extends \PHPUnit_Framework_TestCase
{

    public function testAddingRoute()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('register')
            ->with(
                'route',
                new Route(
                    '/route/',
                    function () {

                    }
                )
            );

        $config = array(
            'container' => array(
                'config' => $this->getMock('\Moss\Config\ConfigInterface'),
                'router' => $router,
                'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
                'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
                'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
                'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
            )
        );

        $kernel = new Kernel($config);
        $kernel->route('route', '/route/', function () { });
    }

    public function testAddingComponent()
    {
        $components = array(
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $this->getMock('\Moss\Http\Router\RouterInterface'),
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface'),
            'someComponent' => $this->getMock('\stdClass')
        );

        $container = $this->getMock('\Moss\Container\ContainerInterface');
        $container->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($name) use ($components) { return $components[$name]; }));

        $container->expects($this->once())
            ->method('register')
            ->with('component', function() {});

        $kernel = new MockKernel($container);
        $kernel->component('component', function () { }, true);
    }

    public function testAddingListener()
    {
        $dispatcher = $this->getMock('\Moss\Dispatcher\DispatcherInterface');
        $dispatcher->expects($this->once())
            ->method('register')
            ->with('event.name', function () { }, 0);

        $config = array(
            'container' => array(
                'config' => $this->getMock('\Moss\Config\ConfigInterface'),
                'router' => $this->getMock('\Moss\Http\Router\RouterInterface'),
                'dispatcher' => $dispatcher,
                'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
                'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
                'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
            )
        );

        $kernel = new Kernel($config);
        $kernel->listener('event.name', function () { }, 0);
    }

    /**
     * @expectedException \Moss\Kernel\KernelException
     * @expectedExceptionMessage No controller was returned from router
     */
    public function testRouterDoesNotReturnAnyController()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(null));

        $config = array(
            'container' => array(
                'config' => $this->getMock('\Moss\Config\ConfigInterface'),
                'router' => $router,
                'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
                'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
                'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
                'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
            )
        );

        $kernel = new Kernel($config);
        $kernel->run();
    }

    public function testRunWithFunctionController()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue('\Moss\Kernel\functionController'));

        $config = array(
            'container' => array(
                'config' => $this->getMock('\Moss\Config\ConfigInterface'),
                'router' => $router,
                'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
                'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
                'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
                'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
            )
        );

        $kernel = new Kernel($config);
        $kernel->run();
    }

    public function testRunWithClosureController()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { return new Response(); }));

        $config = array(
            'container' => array(
                'config' => $this->getMock('\Moss\Config\ConfigInterface'),
                'router' => $router,
                'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
                'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
                'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
                'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
            )
        );

        $kernel = new Kernel($config);
        $kernel->run();
    }

    public function testRunWithStringClassController()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue('\Moss\Kernel\TestController::action'));

        $config = array(
            'container' => array(
                'config' => $this->getMock('\Moss\Config\ConfigInterface'),
                'router' => $router,
                'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
                'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
                'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
                'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
            )
        );

        $kernel = new Kernel($config);
        $kernel->run();
    }

    public function testRunWithArrayClassController()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(array('\Moss\Kernel\TestController', 'action')));

        $config = array(
            'container' => array(
                'config' => $this->getMock('\Moss\Config\ConfigInterface'),
                'router' => $router,
                'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
                'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
                'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
                'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
            )
        );

        $kernel = new Kernel($config);
        $kernel->run();
    }

    /**
     * @expectedException \Moss\Kernel\KernelException
     * @expectedExceptionMessage Invalid class name or class
     */
    public function testRunWithInvalidControllerClass()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(array('Invalid\Controller', 'action')));

        $config = array(
            'container' => array(
                'config' => $this->getMock('\Moss\Config\ConfigInterface'),
                'router' => $router,
                'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
                'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
                'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
                'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
            )
        );

        $kernel = new Kernel($config);
        $kernel->run();
    }

    /**
     * @expectedException \Moss\Kernel\KernelException
     * @expectedExceptionMessage Unable to call action
     */
    public function testRunWithInvalidActionName()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(array('\Moss\Kernel\TestController', 'invalidAction')));

        $config = array(
            'container' => array(
                'config' => $this->getMock('\Moss\Config\ConfigInterface'),
                'router' => $router,
                'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
                'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
                'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
                'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
            )
        );

        $kernel = new Kernel($config);
        $kernel->run();
    }

    /**
     * @expectedException \Moss\Kernel\KernelException
     * @expectedExceptionMessage Invalid controller type
     */
    public function testRunWithInvalidControllerType()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(new \stdClass()));

        $config = array(
            'container' => array(
                'config' => $this->getMock('\Moss\Config\ConfigInterface'),
                'router' => $router,
                'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
                'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
                'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
                'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
            )
        );

        $kernel = new Kernel($config);
        $kernel->run();
    }

    /**
     * @expectedException \Moss\Kernel\KernelException
     * @expectedExceptionMessage There was no response returned from the controller
     */
    public function testControllerDidNotReturnResponse()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { }));

        $config = array(
            'container' => array(
                'config' => $this->getMock('\Moss\Config\ConfigInterface'),
                'router' => $router,
                'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
                'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
                'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
                'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
            )
        );

        $kernel = new Kernel($config);
        $kernel->run();
    }

    /**
     * @expectedException \Moss\Kernel\KernelException
     * @expectedExceptionMessage Response returned from handling "" must be instance of ResponseInterface, got
     */
    public function testControllerReturnedInvalidResponse()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { return new \stdClass(); }));

        $config = array(
            'container' => array(
                'config' => $this->getMock('\Moss\Config\ConfigInterface'),
                'router' => $router,
                'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
                'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
                'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
                'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
            )
        );

        $kernel = new Kernel($config);
        $kernel->run();
    }

    public function testRunEventCalls()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { return new Response(); }));

        $dispatcher = $this->getMock('\Moss\Dispatcher\DispatcherInterface');
        $dispatcher->expects($this->at(0))->method('fire')->with('kernel.request');
        $dispatcher->expects($this->at(1))->method('fire')->with('kernel.route');
        $dispatcher->expects($this->at(2))->method('fire')->with('kernel.controller');
        $dispatcher->expects($this->at(3))->method('fire')->with('kernel.response');
        $dispatcher->expects($this->at(4))->method('fire')->with('kernel.send');

        $config = array(
            'container' => array(
                'config' => $this->getMock('\Moss\Config\ConfigInterface'),
                'router' => $router,
                'dispatcher' => $dispatcher,
                'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
                'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
                'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
            )
        );

        $kernel = new Kernel($config);
        $kernel->run();
    }

    /**
     * @expectedException \Moss\Kernel\NotFoundException
     */
    public function testRunNotFoundEvent()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { throw new NotFoundException(); }));

        $dispatcher = $this->getMock('\Moss\Dispatcher\DispatcherInterface');
        $dispatcher->expects($this->at(0))->method('fire')->with('kernel.request');
        $dispatcher->expects($this->at(1))->method('fire')->with('kernel.route');
        $dispatcher->expects($this->at(2))->method('fire')->with('kernel.controller');
        $dispatcher->expects($this->at(3))->method('fire')->with('kernel.404');

        $config = array(
            'container' => array(
                'config' => $this->getMock('\Moss\Config\ConfigInterface'),
                'router' => $router,
                'dispatcher' => $dispatcher,
                'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
                'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
                'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
            )
        );

        $kernel = new Kernel($config);
        $kernel->run();
    }

    /**
     * @expectedException \Moss\Kernel\ForbiddenException
     */
    public function testRunForbiddenEvent()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { throw new ForbiddenException(); }));

        $dispatcher = $this->getMock('\Moss\Dispatcher\DispatcherInterface');
        $dispatcher->expects($this->at(0))->method('fire')->with('kernel.request');
        $dispatcher->expects($this->at(1))->method('fire')->with('kernel.route');
        $dispatcher->expects($this->at(2))->method('fire')->with('kernel.controller');
        $dispatcher->expects($this->at(3))->method('fire')->with('kernel.403');

        $config = array(
            'container' => array(
                'config' => $this->getMock('\Moss\Config\ConfigInterface'),
                'router' => $router,
                'dispatcher' => $dispatcher,
                'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
                'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
                'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
            )
        );

        $kernel = new Kernel($config);
        $kernel->run();
    }

    /**
     * @expectedException \Exception
     */
    public function testRunInternalErrorEvent()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { new \Exception(); }));

        $dispatcher = $this->getMock('\Moss\Dispatcher\DispatcherInterface');
        $dispatcher->expects($this->at(0))->method('fire')->with('kernel.request');
        $dispatcher->expects($this->at(1))->method('fire')->with('kernel.route');
        $dispatcher->expects($this->at(2))->method('fire')->with('kernel.controller');
        $dispatcher->expects($this->at(3))->method('fire')->with('kernel.500');

        $config = array(
            'container' => array(
                'config' => $this->getMock('\Moss\Config\ConfigInterface'),
                'router' => $router,
                'dispatcher' => $dispatcher,
                'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
                'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
                'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
            )
        );

        $kernel = new Kernel($config);
        $kernel->run();
    }
}
 
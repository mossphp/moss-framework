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

function functionController()
{
    return new Response();
}

class TestController
{
    public static $before;
    public static $after;

    public function before()
    {
        self::$before = true;
    }

    public function after()
    {
        self::$after = true;
    }

    public function action()
    {
        return new Response();
    }

    public function throwException()
    {
        throw new \Exception('Internal error message');
    }

    static public function staticAction()
    {
        return new Response();
    }
}

class MockApp extends App
{
    public function __construct(array $components)
    {
        $this->container = $components['container'];
        $this->dispatcher = $components['dispatcher'];
        $this->router = $components['router'];
        $this->session = $components['session'];
        $this->cookie = $components['cookie'];
        $this->request = $components['request'];
    }
}

class AppTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        TestController::$before = false;
        TestController::$after = false;
    }

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

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $app->route('route', '/route/', function () { });
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
            ->with('component', function () { });

        $components['container'] = $container;

        $app = new MockApp($components);
        $app->component('component', function () { }, true);
    }

    public function testAddingListener()
    {
        $dispatcher = $this->getMock('\Moss\Dispatcher\DispatcherInterface');
        $dispatcher->expects($this->once())
            ->method('register')
            ->with('event.name', function () { }, 0);

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $this->getMock('\Moss\Http\Router\RouterInterface'),
            'dispatcher' => $dispatcher,
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $app->listener('event.name', function () { }, 0);
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage No controller was returned from router
     */
    public function testRouterDoesNotReturnAnyController()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(null));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $app->run();
    }

    public function testRunWithFunctionController()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue('\Moss\Kernel\functionController'));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
    }

    public function testRunWithClosureController()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { return new Response(); }));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
    }

    public function testRunWithStringStaticMethodController()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue('\Moss\Kernel\TestController::staticAction'));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
    }

    public function testRunWithStringInstanceMethodController()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue('\Moss\Kernel\TestController@action'));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
    }

    public function testRunWithStringInstanceMethodControllerWithBeforeAfterMethods()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue('\Moss\Kernel\TestController@action'));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
        $this->assertTrue(TestController::$before);
        $this->assertTrue(TestController::$after);
    }

    public function testRunWithArrayClassController()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(array('\Moss\Kernel\TestController', 'staticAction')));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Invalid class name or class
     */
    public function testRunWithInvalidControllerClass()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue('Invalid\Controller@action'));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $app->run();
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Unable to call action
     */
    public function testRunWithInvalidActionName()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue('\Moss\Kernel\TestController@invalidAction'));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $app->run();
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Invalid controller type
     */
    public function testRunWithInvalidControllerType()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(new \stdClass()));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $app->run();
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage There was no response returned from the controller
     */
    public function testControllerDidNotReturnResponse()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { }));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $app->run();
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Invalid response returned from handling "", expected ResponseInterface, got "stdClass"
     */
    public function testControllerReturnedInvalidResponse()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { return new \stdClass(); }));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $app->run();
    }

    public function testRunEventCalls()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { return new Response(); }));

        $dispatcher = $this->getMock('\Moss\Dispatcher\DispatcherInterface');
        $dispatcher->expects($this->at(0))
            ->method('fire')
            ->with('kernel.request');
        $dispatcher->expects($this->at(1))
            ->method('fire')
            ->with('kernel.route');
        $dispatcher->expects($this->at(2))
            ->method('fire')
            ->with('kernel.controller');
        $dispatcher->expects($this->at(3))
            ->method('fire')
            ->with('kernel.response');
        $dispatcher->expects($this->at(4))
            ->method('fire')
            ->with('kernel.send');

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $dispatcher,
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
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
        $dispatcher->expects($this->at(0))
            ->method('fire')
            ->with('kernel.request');
        $dispatcher->expects($this->at(1))
            ->method('fire')
            ->with('kernel.route');
        $dispatcher->expects($this->at(2))
            ->method('fire')
            ->with('kernel.controller');
        $dispatcher->expects($this->at(3))
            ->method('fire')
            ->with('kernel.404');

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $dispatcher,
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
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
        $dispatcher->expects($this->at(0))
            ->method('fire')
            ->with('kernel.request');
        $dispatcher->expects($this->at(1))
            ->method('fire')
            ->with('kernel.route');
        $dispatcher->expects($this->at(2))
            ->method('fire')
            ->with('kernel.controller');
        $dispatcher->expects($this->at(3))
            ->method('fire')
            ->with('kernel.403');

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $dispatcher,
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Internal error message
     */
    public function testRunInternalErrorEvent()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue('\Moss\Kernel\TestController@throwException'));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $app->run();
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Received response is not an instance of ResponseInterface
     */
    public function testEventReturnsInvalidResponse()
    {
        $dispatcher = $this->getMock('\Moss\Dispatcher\DispatcherInterface');
        $dispatcher->expects($this->at(0))
            ->method('fire')
            ->will($this->returnValue(new \stdClass()));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $this->getMock('\Moss\Http\Router\RouterInterface'),
            'dispatcher' => $dispatcher,
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $app->run();
    }

    public function testKernelRequestReturnsResponse()
    {
        $dispatcher = $this->getMock('\Moss\Dispatcher\DispatcherInterface');
        $dispatcher->expects($this->at(0))
            ->method('fire')
            ->will($this->returnValue(new Response('Event response')));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $this->getMock('\Moss\Http\Router\RouterInterface'),
            'dispatcher' => $dispatcher,
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $this->assertEquals(
            'Event response', $app->run()
                ->content()
        );
    }

    public function testKernelRouteReturnsResponse()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { return new Response(); }));

        $dispatcher = $this->getMock('\Moss\Dispatcher\DispatcherInterface');
        $dispatcher->expects($this->at(0))
            ->method('fire')
            ->with('kernel.request');
        $dispatcher->expects($this->at(1))
            ->method('fire')
            ->will($this->returnValue(new Response('Event response')));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $dispatcher,
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $this->assertEquals(
            'Event response', $app->run()
                ->content()
        );
    }

    public function testKernelControllerReturnsResponse()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { return new Response(); }));

        $dispatcher = $this->getMock('\Moss\Dispatcher\DispatcherInterface');
        $dispatcher->expects($this->at(0))
            ->method('fire')
            ->with('kernel.request');
        $dispatcher->expects($this->at(1))
            ->method('fire')
            ->with('kernel.route');
        $dispatcher->expects($this->at(2))
            ->method('fire')
            ->will($this->returnValue(new Response('Event response')));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $dispatcher,
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $this->assertEquals(
            'Event response', $app->run()
                ->content()
        );
    }

    public function testKernelResponseReturnsResponse()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { return new Response(); }));

        $dispatcher = $this->getMock('\Moss\Dispatcher\DispatcherInterface');
        $dispatcher->expects($this->at(0))
            ->method('fire')
            ->with('kernel.request');
        $dispatcher->expects($this->at(1))
            ->method('fire')
            ->with('kernel.route');
        $dispatcher->expects($this->at(2))
            ->method('fire')
            ->with('kernel.controller');
        $dispatcher->expects($this->at(3))
            ->method('fire')
            ->will($this->returnValue(new Response('Event response')));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $dispatcher,
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $this->assertEquals(
            'Event response', $app->run()
                ->content()
        );
    }

    public function testKernelSendReturnsResponse()
    {
        $router = $this->getMock('\Moss\Http\Router\RouterInterface');
        $router->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { return new Response(); }));

        $dispatcher = $this->getMock('\Moss\Dispatcher\DispatcherInterface');
        $dispatcher->expects($this->at(0))
            ->method('fire')
            ->with('kernel.request');
        $dispatcher->expects($this->at(1))
            ->method('fire')
            ->with('kernel.route');
        $dispatcher->expects($this->at(2))
            ->method('fire')
            ->with('kernel.controller');
        $dispatcher->expects($this->at(3))
            ->method('fire')
            ->with('kernel.response');
        $dispatcher->expects($this->at(4))
            ->method('fire')
            ->will($this->returnValue(new Response('Event response')));

        $components = array(
            'container' => $this->getMock('\Moss\Container\ContainerInterface'),
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $router,
            'dispatcher' => $dispatcher,
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        );

        $app = new MockApp($components);
        $this->assertEquals(
            'Event response', $app->run()
                ->content()
        );
    }
}
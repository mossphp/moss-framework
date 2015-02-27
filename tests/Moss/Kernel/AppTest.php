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


use Moss\Container\ContainerInterface;
use Moss\Http\Response\Response;
use Moss\Http\Router\Route;

function functionController()
{
    return new Response();
}

class TestController
{
    public static $before;
    public static $beforeResponse;

    public static $after;
    public static $afterResponse;

    public function before()
    {
        self::$before = true;
        return self::$beforeResponse;
    }

    public function after()
    {
        self::$after = true;
        return self::$afterResponse;
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
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}

class AppTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $components;

    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    protected function setUp()
    {
        parent::setUp();

        TestController::$before = false;
        TestController::$beforeResponse = null;
        TestController::$after = false;
        TestController::$afterResponse = null;

        $this->components = [
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $this->getMock('\Moss\Http\Router\RouterInterface'),
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface')
        ];

        $componentsMap = [
            ['config', & $this->components['config']],
            ['router', & $this->components['router']],
            ['dispatcher', & $this->components['dispatcher']],
            ['session', & $this->components['session']],
            ['cookie', & $this->components['cookie']],
            ['request', & $this->components['request']]
        ];

        $this->container = $this->getMock('\Moss\Container\ContainerInterface');
        $this->container->expects($this->any())->method('get')->willReturnMap($componentsMap);
    }

    public function testRetrievingComponentTroughGetMethod()
    {
        $this->container->expects($this->once())->method('get')->with('config');

        $app = new MockApp($this->container);
        $app->get('config');
    }

    public function testRetrievingComponentTroughMagicProperty()
    {
        $this->container->expects($this->once())->method('get')->with('config');

        $app = new MockApp($this->container);
        $app->config;
    }

    public function testRetrievingContainer()
    {
        $app = new MockApp($this->container);
        $this->assertInstanceOf('\Moss\Container\ContainerInterface', $app->container());
    }

    public function testRetrievingConfig()
    {
        $this->container->expects($this->once())->method('get')->with('config');

        $app = new MockApp($this->container);
        $this->assertInstanceOf('\Moss\Config\ConfigInterface', $app->config());
    }

    public function testRetrievingRouter()
    {
        $this->container->expects($this->once())->method('get')->with('router');

        $app = new MockApp($this->container);
        $this->assertInstanceOf('\Moss\Http\Router\RouterInterface', $app->router());
    }

    public function testRetrievingDispatcher()
    {
        $this->container->expects($this->once())->method('get')->with('dispatcher');

        $app = new MockApp($this->container);
        $this->assertInstanceOf('\Moss\Dispatcher\DispatcherInterface', $app->dispatcher());
    }

    public function testRetrievingRequest()
    {
        $this->container->expects($this->once())->method('get')->with('request');

        $app = new MockApp($this->container);
        $this->assertInstanceOf('\Moss\Http\Request\RequestInterface', $app->request());
    }

    public function testRetrievingSession()
    {
        $this->container->expects($this->once())->method('get')->with('session');

        $app = new MockApp($this->container);
        $this->assertInstanceOf('\Moss\Http\Session\SessionInterface', $app->session());
    }

    public function testRetrievingCookie()
    {
        $this->container->expects($this->once())->method('get')->with('cookie');

        $app = new MockApp($this->container);
        $this->assertInstanceOf('\Moss\Http\Cookie\CookieInterface', $app->cookie());
    }

    public function testAddingRoute()
    {
        $this->components['router']->expects($this->once())
            ->method('register')
            ->with(
                'route',
                new Route(
                    '/route/',
                    function () {

                    }
                )
            );

        $app = new MockApp($this->container);
        $app->route('route', '/route/', function () { });
    }

    public function testAddingComponent()
    {
        $this->container->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));

        $this->container->expects($this->once())
            ->method('register')
            ->with('component', function () { });

        $app = new MockApp($this->container);
        $app->component('component', function () { }, true);
    }

    public function testAddingListener()
    {
        $this->components['dispatcher']->expects($this->once())
            ->method('register')
            ->with('event.name', function () { }, 0);

        $app = new MockApp($this->container);
        $app->listener('event.name', function () { }, 0);
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage No controller was returned from router
     */
    public function testRouterDoesNotReturnAnyController()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue(null));

        $app = new MockApp($this->container);
        $app->run();
    }

    public function testRunWithFunctionController()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue('\Moss\Kernel\functionController'));

        $app = new MockApp($this->container);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
    }

    public function testRunWithClosureController()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { return new Response(); }));

        $app = new MockApp($this->container);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
    }

    public function testRunWithStringStaticMethodController()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue('\Moss\Kernel\TestController::staticAction'));

        $app = new MockApp($this->container);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
    }

    public function testRunWithStringInstanceMethodController()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue('\Moss\Kernel\TestController@action'));

        $app = new MockApp($this->container);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
    }

    public function testRunWithStringInstanceMethodControllerWithBeforeAfterMethods()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue('\Moss\Kernel\TestController@action'));

        $app = new MockApp($this->container);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
        $this->assertTrue(TestController::$before);
        $this->assertTrue(TestController::$after);
    }

    public function testRuntWithBeforeReturningResponse()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue('\Moss\Kernel\TestController@action'));

        TestController::$beforeResponse = $this->getMock('\Moss\Http\Response\ResponseInterface');

        $app = new MockApp($this->container);
        $response = $app->run();

        $this->assertSame(TestController::$beforeResponse, $response);
    }

    public function testRuntWithAfterReturningResponse()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue('\Moss\Kernel\TestController@action'));

        TestController::$afterResponse = $this->getMock('\Moss\Http\Response\ResponseInterface');

        $app = new MockApp($this->container);
        $response = $app->run();

        $this->assertSame(TestController::$afterResponse, $response);
    }

    public function testRunWithArrayClassController()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue(['\Moss\Kernel\TestController', 'staticAction']));

        $app = new MockApp($this->container);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Invalid class name or class
     */
    public function testRunWithInvalidControllerClass()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue('Invalid\Controller@action'));

        $app = new MockApp($this->container);
        $app->run();
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Unable to call action
     */
    public function testRunWithInvalidActionName()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue('\Moss\Kernel\TestController@invalidAction'));

        $app = new MockApp($this->container);
        $app->run();
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Invalid controller type
     */
    public function testRunWithInvalidControllerType()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue(new \stdClass()));

        $app = new MockApp($this->container);
        $app->run();
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage There was no response returned from the controller
     */
    public function testControllerDidNotReturnResponse()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { }));

        $app = new MockApp($this->container);
        $app->run();
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Invalid response returned from handling "", expected ResponseInterface, got "stdClass"
     */
    public function testControllerReturnedInvalidResponse()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { return new \stdClass(); }));

        $app = new MockApp($this->container);
        $app->run();
    }

    public function testRunEventCalls()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { return new Response(); }));

        $this->components['dispatcher']->expects($this->at(0))
            ->method('fire')
            ->with('kernel.request');
        $this->components['dispatcher']->expects($this->at(1))
            ->method('fire')
            ->with('kernel.route');
        $this->components['dispatcher']->expects($this->at(2))
            ->method('fire')
            ->with('kernel.controller');
        $this->components['dispatcher']->expects($this->at(3))
            ->method('fire')
            ->with('kernel.response');
        $this->components['dispatcher']->expects($this->at(4))
            ->method('fire')
            ->with('kernel.send');

        $app = new MockApp($this->container);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
    }

    /**
     * @expectedException \Moss\Kernel\NotFoundException
     */
    public function testRunNotFoundEvent()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { throw new NotFoundException(); }));

        $this->components['dispatcher']->expects($this->at(0))
            ->method('fire')
            ->with('kernel.request');
        $this->components['dispatcher']->expects($this->at(1))
            ->method('fire')
            ->with('kernel.route');
        $this->components['dispatcher']->expects($this->at(2))
            ->method('fire')
            ->with('kernel.controller');
        $this->components['dispatcher']->expects($this->at(3))
            ->method('fire')
            ->with('kernel.404');

        $app = new MockApp($this->container);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
    }

    /**
     * @expectedException \Moss\Kernel\ForbiddenException
     */
    public function testRunForbiddenEvent()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { throw new ForbiddenException(); }));

        $this->components['dispatcher']->expects($this->at(0))
            ->method('fire')
            ->with('kernel.request');
        $this->components['dispatcher']->expects($this->at(1))
            ->method('fire')
            ->with('kernel.route');
        $this->components['dispatcher']->expects($this->at(2))
            ->method('fire')
            ->with('kernel.controller');
        $this->components['dispatcher']->expects($this->at(3))
            ->method('fire')
            ->with('kernel.403');

        $app = new MockApp($this->container);
        $response = $app->run();

        $this->assertInstanceOf('\Moss\Http\Response\ResponseInterface', $response);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Internal error message
     */
    public function testRunInternalErrorEvent()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue('\Moss\Kernel\TestController@throwException'));

        $app = new MockApp($this->container);
        $app->run();
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Received response is not an instance of ResponseInterface
     */
    public function testEventReturnsInvalidResponse()
    {
        $this->components['dispatcher']->expects($this->at(0))
            ->method('fire')
            ->will($this->returnValue(new \stdClass()));

        $app = new MockApp($this->container);
        $app->run();
    }

    public function testKernelRequestReturnsResponse()
    {
        $this->components['dispatcher']->expects($this->at(0))
            ->method('fire')
            ->will($this->returnValue(new Response('Event response')));

        $app = new MockApp($this->container);
        $this->assertEquals(
            'Event response', $app->run()
            ->content()
        );
    }

    public function testKernelRouteReturnsResponse()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { return new Response(); }));

        $this->components['dispatcher']->expects($this->at(0))
            ->method('fire')
            ->with('kernel.request');
        $this->components['dispatcher']->expects($this->at(1))
            ->method('fire')
            ->will($this->returnValue(new Response('Event response')));

        $app = new MockApp($this->container);
        $this->assertEquals(
            'Event response', $app->run()
            ->content()
        );
    }

    public function testKernelControllerReturnsResponse()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { return new Response(); }));

        $this->components['dispatcher']->expects($this->at(0))
            ->method('fire')
            ->with('kernel.request');
        $this->components['dispatcher']->expects($this->at(1))
            ->method('fire')
            ->with('kernel.route');
        $this->components['dispatcher']->expects($this->at(2))
            ->method('fire')
            ->will($this->returnValue(new Response('Event response')));

        $app = new MockApp($this->container);
        $this->assertEquals(
            'Event response', $app->run()
            ->content()
        );
    }

    public function testKernelResponseReturnsResponse()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { return new Response(); }));

        $this->components['dispatcher']->expects($this->at(0))
            ->method('fire')
            ->with('kernel.request');
        $this->components['dispatcher']->expects($this->at(1))
            ->method('fire')
            ->with('kernel.route');
        $this->components['dispatcher']->expects($this->at(2))
            ->method('fire')
            ->with('kernel.controller');
        $this->components['dispatcher']->expects($this->at(3))
            ->method('fire')
            ->will($this->returnValue(new Response('Event response')));

        $app = new MockApp($this->container);
        $this->assertEquals(
            'Event response', $app->run()
            ->content()
        );
    }

    public function testKernelSendReturnsResponse()
    {
        $this->components['router']->expects($this->once())
            ->method('match')
            ->will($this->returnValue(function () { return new Response(); }));

        $this->components['dispatcher']->expects($this->at(0))
            ->method('fire')
            ->with('kernel.request');
        $this->components['dispatcher']->expects($this->at(1))
            ->method('fire')
            ->with('kernel.route');
        $this->components['dispatcher']->expects($this->at(2))
            ->method('fire')
            ->with('kernel.controller');
        $this->components['dispatcher']->expects($this->at(3))
            ->method('fire')
            ->with('kernel.response');
        $this->components['dispatcher']->expects($this->at(4))
            ->method('fire')
            ->will($this->returnValue(new Response('Event response')));

        $app = new MockApp($this->container);
        $this->assertEquals(
            'Event response', $app->run()
            ->content()
        );
    }
}

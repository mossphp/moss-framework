<?php
namespace Moss\Http\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Router
     */
    protected $router;

    protected function setUp()
    {
        $this->router = new Router();

        $route = new Route('/router/{foo}/({bar}/)', 'router:foo:bar');
        $route->requirements(array('foo' => '\w+', 'bar' => '\d*(\/)?'));
        $this->router->register('router_foo_bar', $route);

        $route = new Route('/router/{foo}/', 'router:foo');
        $route->requirements(array('foo' => '\w+'));
        $this->router->register('router_foo', $route);

        $route = new Route('/router/', 'router');
        $this->router->register('router', $route);

        $route = new Route('/router/', 'domain:router');
        $route->host('domain.{basename}');
        $this->router->register('domain_router', $route);
    }

    protected function mockRequest($controller, $path, $host = null)
    {
        $bag = $this->getMock('Moss\Http\bag\BagInterface');

        $request = $this->getMock('Moss\Http\request\RequestInterface');

        $request
            ->expects($this->any())
            ->method('query')
            ->will($this->returnValue($bag));

        $request
            ->expects($this->any())
            ->method('controller')
            ->will($this->returnValue($controller));

        $request
            ->expects($this->any())
            ->method('baseName')
            ->will($this->returnValue('http://test.com'));

        $request
            ->expects($this->any())
            ->method('path')
            ->will($this->returnValue($path));

        $request
            ->expects($this->any())
            ->method('host')
            ->will($this->returnValue($host));

        return $request;
    }

    public function testRetrieve()
    {
        $expected = array();

        $route = new Route('/router/{foo}/({bar}/)', 'router:foo:bar');
        $route->requirements(array('foo' => '\w+', 'bar' => '\d*(\/)?'));
        $expected['router_foo_bar'] = $route;

        $route = new Route('/router/{foo}/', 'router:foo');
        $route->requirements(array('foo' => '\w+'));
        $expected['router_foo'] = $route;

        $route = new Route('/router/', 'router');
        $expected['router'] = $route;

        $route = new Route('/router/', 'domain:router');
        $route->host('domain.{basename}');
        $expected['domain_router'] = $route;

        $this->assertEquals($expected, $this->router->retrieve());
    }

    /**
     * @expectedException \Moss\Http\Router\RouterException
     * @expectedExceptionMessage Route for "/missing-route/" not found!
     */
    public function testMatchRouteNotExists()
    {
        $this->router->match($this->mockRequest('missing-route', '/missing-route/'));
    }

    public function testMatchWithMultipleArguments()
    {
        $controller = $this->router->match($this->mockRequest('router:foo:bar', '/router/foo/123/', null));
        $this->assertEquals('router:foo:bar', $controller);
    }

    public function testMatchWithoutOptionalArguments()
    {
        $controller = $this->router->match($this->mockRequest('router:foo:bar', '/router/foo/', null));
        $this->assertEquals('router:foo:bar', $controller);
    }

    public function testMatchWithSingleArgument()
    {
        $controller = $this->router->match($this->mockRequest('router:foo', '/router/foo/', null));
        $this->assertEquals('router:foo', $controller);
    }

    public function testMatchWithoutArguments()
    {
        $controller = $this->router->match($this->mockRequest('router', '/router/', null));
        $this->assertEquals('router', $controller);
    }

    public function testMatchWithDomain()
    {
        $controller = $this->router->match($this->mockRequest('domain:router', '/router/', 'http://domain.test.com'));
        $this->assertEquals('domain:router', $controller);
    }

    /**
     * @dataProvider matchProvider
     */
    public function testMatchWithEndingSlash($controller, $uri, $host = null)
    {
        $result = $this->router->match($this->mockRequest($controller, rtrim($uri, '/'), $host));
        $this->assertEquals($controller, $result);
    }

    /**
     * @dataProvider matchProvider
     */
    public function testMatchWithWithoutSlash($controller, $uri, $host = null)
    {
        $result = $this->router->match($this->mockRequest($controller, rtrim($uri, '/').'/', $host));
        $this->assertEquals($controller, $result);
    }

    public function matchProvider()
    {
        return array(
            array('router:foo:bar', '/router/foo/123/', null),
            array('router:foo:bar', '/router/foo/', null),
            array('router:foo', '/router/foo/', null),
            array('router', '/router/', null),
            array('domain:router', '/router/', 'http://domain.test.com'),
            array('router:foo', '/router/foo/', null),
            array('router:foo', '/router/foo', null),
        );
    }

    public function testDefaults()
    {
        $request = $this->getMock('Moss\Http\Request\RequestInterface');

        $request
            ->expects($this->any())
            ->method('query')
            ->will($this->returnValue($this->getMock('Moss\Http\Bag\BagInterface')));

        $request
            ->expects($this->any())
            ->method('path')
            ->will($this->returnValue('/router/foo'));

        $request
            ->expects($this->any())
            ->method('baseName')
            ->will($this->returnValue('http://test.com'));

        $request
            ->expects($this->any())
            ->method('controller')
            ->will($this->returnValue('router:foo:bar'));

        $request
            ->expects($this->any())
            ->method('locale')
            ->will($this->returnValue('fr'));

        $request
            ->expects($this->any())
            ->method('format')
            ->will($this->returnValue('yml'));

        $this->router->match($request);

        $expected = array(
            'host' => 'http://test.com',
            'route' => 'router_foo_bar',
            'locale' => 'fr',
            'format' => 'yml'
        );

        $this->assertAttributeEquals($expected, 'defaults', $this->router);
    }

    /**
     * @expectedException \Moss\Http\Router\RouterException
     * @expectedExceptionMessage Unable to make "self" url - default route was not defined.
     */
    public function testMakeWithoutDefaultRoute()
    {
        $this->router->make();
    }

    public function testMakeWithDefaultRoute()
    {
        $this->router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
        $this->assertEquals('http://test.com/router/foo/123/', $this->router->make(null, array('foo' => 'foo', 'bar' => 123)));
    }

    public function testMakeByName()
    {
        $this->router->match($this->mockRequest('router:foo:bar', '/router/foo/123/', 'http://test.com/'));
        $this->assertEquals('http://domain.test.com/router/', $this->router->make('domain_router'));
    }

    public function testMakeByController()
    {
        $this->router->match($this->mockRequest('router:foo:bar', '/router/foo/123/', 'http://test.com/'));
        $this->assertEquals('http://domain.test.com/router/', $this->router->make('domain:router'));
    }

    /**
     * @expectedException \Moss\Http\Router\RouterException
     * @expectedExceptionMessage Unable to make url, matching route for "invalid_controller" not found
     */
    public function testMakeWithInvalidController()
    {
        $this->router->match($this->mockRequest('router:foo:bar', '/router/foo/123/', 'http://test.com/'));
        $this->router->make('invalid_controller');
    }

    public function testMake()
    {
        $this->router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
        $this->assertEquals('http://test.com/router/foo/123/', $this->router->make('router:foo:bar', array('foo' => 'foo', 'bar' => 123), false, false));
    }

    public function testMakeWithQuery()
    {
        $this->router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
        $this->assertEquals('http://test.com/router/foo/123/?yada=yada', $this->router->make('router:foo:bar', array('foo' => 'foo', 'bar' => 123, 'yada' => 'yada')));
    }

    public function testMakeWithoutOptionalArguments()
    {
        $this->router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
        $this->assertEquals('http://test.com/router/foo/', $this->router->make('router:foo:bar', array('foo' => 'foo')));
    }

    public function testMakeWithOptionalArguments()
    {
        $this->router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
        $this->assertEquals('http://test.com/router/foo/123/', $this->router->make('router:foo:bar', array('foo' => 'foo', 'bar' => 123)));
    }

    public function testMakeWithHost()
    {
        $this->router->match($this->mockRequest('router:foo:bar', '/router/foo/123/', 'http://test.com/'));
        $this->assertEquals('http://domain.test.com/router/', $this->router->make('domain:router'));
    }
}
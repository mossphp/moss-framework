<?php
namespace moss\router;

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

    protected function mockRequest($controller, $url, $host = null)
    {
        $request = $this->getMock('moss\http\request\RequestInterface');

        $request
            ->expects($this->any())
            ->method('controller')
            ->will($this->returnValue($controller));

        $request
            ->expects($this->any())
            ->method('baseName')
            ->will($this->returnValue('http://test.com/'));

        $request
            ->expects($this->any())
            ->method('url')
            ->will($this->returnValue($url));

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
     * @expectedException \moss\router\RouterException
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

    public function testMatchQuery()
    {
        $request = $this->getMock('moss\http\request\RequestInterface');

        $request
            ->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue('router'));

        $request
            ->expects($this->any())
            ->method('controller')
            ->will($this->returnValue('router'));

        $request
            ->expects($this->any())
            ->method('baseName')
            ->will($this->returnValue('http://test.com'));

        $request
            ->expects($this->any())
            ->method('locale')
            ->will($this->returnValue('fr'));

        $request
            ->expects($this->any())
            ->method('format')
            ->will($this->returnValue('yml'));

        $controller = $this->router->match($request);
        $this->assertEquals('router', $controller);
        $this->assertAttributeEquals(
             array(
                  'host' => 'http://test.com',
                  'controller' => 'router',
                  'locale' => 'fr',
                  'format' => 'yml'
             ), 'defaults', $this->router
        );
    }


    /**
     * @expectedException \moss\router\RouterException
     * @expectedExceptionMessage Unable to make 'self' url - default controller is not defined.
     */
    public function testMakeWithoutDefaultController()
    {
        $this->router->make();
    }

    public function testMakeWithDefaultController()
    {
        $this->router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
        $this->assertEquals('http://test.com/router/foo/123/', $this->router->make(null, array('foo' => 'foo', 'bar' => 123)));
    }

    public function testMakeNormal()
    {
        $this->router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
        $this->assertEquals('http://test.com/?controller=router_foo_bar&foo=foo&bar=123', $this->router->make('router:foo:bar', array('foo' => 'foo', 'bar' => 123), true, false));
    }

    public function testMakeUnknown()
    {
        $this->router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
        $this->assertEquals('http://test.com/?controller=router_foo_bar', $this->router->make('router:foo:bar'));
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

    public function testMakeRelative()
    {
        $this->router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
        $this->assertEquals('./router/foo/123/', $this->router->make('router:foo:bar', array('foo' => 'foo', 'bar' => 123), false, true));
    }

    public function testMakeAbsolute()
    {
        $this->router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
        $this->assertEquals('http://test.com/router/foo/123/', $this->router->make('router:foo:bar', array('foo' => 'foo', 'bar' => 123), false, false));
    }

    public function testMakeAbsoluteWithQuery()
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
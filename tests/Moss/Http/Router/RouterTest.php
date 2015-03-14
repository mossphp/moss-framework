<?php
namespace Moss\Http\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Router
     */
    protected $router;

    protected function mockRequest($path, $host = 'test.com')
    {
        $bag = $this->getMock('Moss\Bag\BagInterface');

        $request = $this->getMock('Moss\Http\request\RequestInterface');

        $request
            ->expects($this->any())
            ->method('query')
            ->will($this->returnValue($bag));

        $request
            ->expects($this->any())
            ->method('schema')
            ->will($this->returnValue('http'));

        $request
            ->expects($this->any())
            ->method('host')
            ->will($this->returnValue($host));

        $request
            ->expects($this->any())
            ->method('baseName')
            ->will($this->returnValue('http://'.$host));

        $request
            ->expects($this->any())
            ->method('path')
            ->will($this->returnValue($path));

        return $request;
    }

    public function testRetrieve()
    {
        $route = new Route('/router/', 'router');
        $expected['route'] = $route;

        $router = new Router();
        $router->register('route', $route);
        $this->assertEquals($expected, $router->retrieve());
    }

    /**
     * @expectedException \Moss\Http\Router\RouterException
     * @expectedExceptionMessage Route for "/missing-route/" not found!
     */
    public function testMatchRouteNotExists()
    {
        $router = new Router();
        $router->match($this->mockRequest('/missing-route/'));
    }

    public function testMatchWithMultipleArguments()
    {
        $router = new Router();
        $router->register('route', new Route('/router/{foo:\w}/({bar:\d})/', 'controller'));

        $controller = $router->match($this->mockRequest('/router/foo/123/', null));

        $this->assertEquals('controller', $controller);
    }

    public function testMatchWithoutOptionalArguments()
    {
        $router = new Router();
        $router->register('route', new Route('/router/{foo:\w}/({bar:\d})/', 'controller'));

        $controller = $router->match($this->mockRequest('/router/foo/', null));

        $this->assertEquals('controller', $controller);
    }

    public function testMatchWithSingleArgument()
    {
        $router = new Router();
        $router->register('route', new Route('/router/{foo:\w}/', 'controller'));

        $controller = $router->match($this->mockRequest('/router/foo/', null));
        $this->assertEquals('controller', $controller);
    }

    public function testMatchWithoutArguments()
    {
        $router = new Router();
        $router->register('route', new Route('/router/', 'controller'));

        $controller = $router->match($this->mockRequest('/router/', null));

        $this->assertEquals('controller', $controller);
    }

    public function testMatchWithDomain()
    {
        $route = new Route('/router/', 'controller');
        $route->host('domain.test.com');

        $router = new Router();
        $router->register('route', $route);

        $controller = $router->match($this->mockRequest('/router/', 'domain.test.com'));
        $this->assertEquals('controller', $controller);
    }

    public function testMatchWithAdaptiveDomain()
    {
        $route = new Route('/router/', 'controller');
        $route->host('domain.{basename}');

        $router = new Router();
        $router->register('route', $route);

        $controller = $router->match($this->mockRequest('/router/', 'domain.test.com'));
        $this->assertEquals('controller', $controller);
    }

    /**
     * @dataProvider matchProvider
     */
    public function testMatchWithEndingSlash($expected, $uri, $host = null)
    {
        $router = new Router();

        $route = new Route('/router/', 'domain_router');
        $route->host('domain.{basename}');
        $router->register('domain_router', $route);

        $route = new Route('/router/foo-foo/({bar:\d})/', 'router_foo_bar');
        $router->register('router_foo_bar', $route);

        $route = new Route('/router/{foo:\w}/', 'router_foo');
        $router->register('router_foo', $route);

        $route = new Route('/router/', 'router');
        $router->register('router', $route);

        $controller = $router->match($this->mockRequest(rtrim($uri, '/'), $host));
        $this->assertEquals($expected, $controller);
    }

    /**
     * @dataProvider matchProvider
     */
    public function testMatchWithWithoutSlash($expected, $uri, $host = null)
    {
        $router = new Router();

        $route = new Route('/router/', 'domain_router');
        $route->host('domain.{basename}');
        $router->register('domain_router', $route);

        $route = new Route('/router/foo-foo/({bar:\d})/', 'router_foo_bar');
        $router->register('router_foo_bar', $route);

        $route = new Route('/router/{foo:\w}/', 'router_foo');
        $router->register('router_foo', $route);

        $route = new Route('/router/', 'router');
        $router->register('router', $route);

        $controller = $router->match($this->mockRequest(rtrim($uri, '/') . '/', $host));
        $this->assertEquals($expected, $controller);
    }

    public function matchProvider()
    {
        return [
            ['router_foo_bar', '/router/foo-foo/123/', null],
            ['router_foo', '/router/foo/', null],
            ['router', '/router/', null],
            ['domain_router', '/router/', 'http://domain.test.com'],
        ];
    }

    public function testDefaults()
    {
        $request = $this->getMock('Moss\Http\Request\RequestInterface');

        $request
            ->expects($this->any())
            ->method('query')
            ->will($this->returnValue($this->getMock('Moss\Bag\BagInterface')));

        $request
            ->expects($this->any())
            ->method('path')
            ->will($this->returnValue('/router/'));

        $request
            ->expects($this->any())
            ->method('baseName')
            ->will($this->returnValue('http://test.com'));

        $request
            ->expects($this->any())
            ->method('controller')
            ->will($this->returnValue('controller'));

        $request
            ->expects($this->any())
            ->method('language')
            ->will($this->returnValue('fr'));

        $request
            ->expects($this->any())
            ->method('format')
            ->will($this->returnValue('yml'));

        $router = new Router();
        $router->register('router', new Route('/router/', 'router'));
        $router->match($request);

        $expected = [
            'host' => 'http://test.com',
            'route' => 'router',
            'language' => 'fr',
            'format' => 'yml'
        ];

        $this->assertAttributeEquals($expected, 'defaults', $router);
    }

    /**
     * @expectedException \Moss\Http\Router\RouterException
     * @expectedExceptionMessage Unable to make "self" url - default route was not defined.
     */
    public function testMakeWithoutDefaultRoute()
    {
        $router = new Router();
        $router->make();
    }

    public function testMakeWithDefaultRoute()
    {
        $router = new Router();

        $route = new Route('/router/{foo:\w}/({bar:\d})/', 'router_foo_bar');
        $router->register('route', $route);

        $router->match($this->mockRequest('/router/foo/123/'));
        $this->assertEquals('http://test.com/router/foo/123/', $router->make(null, ['foo' => 'foo', 'bar' => 123]));
    }

    public function testMakeByName()
    {
        $router = new Router();

        $route = new Route('/router/{foo:\w}/({bar:\d})/', 'router_foo_bar');
        $router->register('route', $route);

        $route = new Route('/router/', 'domain_router');
        $route->host('domain.{basename}');
        $router->register('domain_router', $route);

        $router->match($this->mockRequest('/router/foo/123/', 'test.com'));
        $this->assertEquals('http://domain.test.com/router/', $router->make('domain_router'));
    }

    public function testMakeByController()
    {
        $router = new Router();

        $route = new Route('/router/{foo:\w}/({bar:\d})/', 'router_foo_bar');
        $router->register('route', $route);

        $route = new Route('/router/', 'domain_router');
        $route->host('domain.{basename}');
        $router->register('domain_router', $route);

        $router->match($this->mockRequest('/router/foo/123/', 'test.com'));
        $this->assertEquals('http://domain.test.com/router/', $router->make('domain_router'));
    }

    public function testMake()
    {
        $router = new Router();

        $route = new Route('/router/{foo:\w}/({bar:\d})/', 'router_foo_bar');
        $router->register('route', $route);

        $router->match($this->mockRequest('/router/foo/123/'));
        $this->assertEquals('http://test.com/router/foo/123/', $router->make('route', ['foo' => 'foo', 'bar' => 123], false, false));
    }

    public function testMakeWithQuery()
    {
        $router = new Router();

        $route = new Route('/router/{foo:\w}/({bar:\d})/', 'router_foo_bar');
        $router->register('route', $route);

        $router->match($this->mockRequest('/router/foo/123/'));
        $this->assertEquals('http://test.com/router/foo/123/?yada=yada', $router->make('route', ['foo' => 'foo', 'bar' => 123, 'yada' => 'yada']));
    }

    public function testMakeWithoutOptionalArguments()
    {
        $router = new Router();

        $route = new Route('/router/{foo:\w}/({bar:\d})/', 'router_foo_bar');
        $router->register('route', $route);

        $router->match($this->mockRequest('/router/foo/123/'));
        $this->assertEquals('http://test.com/router/foo/', $router->make('route', ['foo' => 'foo']));
    }

    public function testMakeWithOptionalArguments()
    {
        $router = new Router();

        $route = new Route('/router/{foo:\w}/({bar:\d})/', 'router_foo_bar');
        $router->register('route', $route);

        $router->match($this->mockRequest('/router/foo/123/'));
        $this->assertEquals('http://test.com/router/foo/123/', $router->make('route', ['foo' => 'foo', 'bar' => 123]));
    }
}

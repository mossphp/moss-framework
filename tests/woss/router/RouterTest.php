<?php
namespace moss\router;

class RouterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Router
	 */
	protected $Router;

	protected function setUp() {
		$this->Router = new Router(true);

		$Route = new Route('/router/{foo}/({bar})/', 'router:foo:bar');
		$Route->requirements(array('foo' => '\w+', 'bar' => '\d*'));
		$this->Router->register('router_foo_bar', $Route);

		$Route = new Route('/router/{foo}/', 'router:foo');
		$Route->requirements(array('foo' => '\w+'));
		$this->Router->register('router_foo', $Route);

		$Route = new Route('/router/', 'router');
		$this->Router->register('router', $Route);

		$Route = new Route('/router/', 'domain:router');
		$Route->host('domain.{basename}');
		$this->Router->register('domain_router', $Route);
	}

	protected function mockRequest($controller, $url, $host = null) {
		$Request = $this->getMock('moss\http\request\RequestInterface');

		$Request
			->expects($this->any())
			->method('controller')
			->will($this->returnValue($controller));

		$Request
			->expects($this->any())
			->method('baseName')
			->will($this->returnValue('http://test.com/'));

		$Request
			->expects($this->any())
			->method('url')
			->will($this->returnValue($url));

		$Request
			->expects($this->any())
			->method('host')
			->will($this->returnValue($host));

		return $Request;
	}

	public function testRetrieve() {
		$expected = array();

		$Route = new Route('/router/{foo}/({bar})/', 'router:foo:bar');
		$Route->requirements(array('foo' => '\w+', 'bar' => '\d*'));
		$expected['router_foo_bar'] = $Route;

		$Route = new Route('/router/{foo}/', 'router:foo');
		$Route->requirements(array('foo' => '\w+'));
		$expected['router_foo'] = $Route;

		$Route = new Route('/router/', 'router');
		$expected['router'] = $Route;

		$Route = new Route('/router/', 'domain:router');
		$Route->host('domain.{basename}');
		$expected['domain_router'] = $Route;

		$this->assertEquals($expected, $this->Router->retrieve());
	}

	/**
	 * @expectedException \moss\router\RouterException
	 */
	public function testMatchRouteNotExists() {
		$this->Router->match($this->mockRequest('missing-route', '/missing-route/'));
	}

	public function testMatchWithMultipleArguments() {
		$controller = $this->Router->match($this->mockRequest('router:foo:bar', '/router/foo/123/', null));
		$this->assertEquals('router:foo:bar', $controller);
	}

	public function testMatchWithoutOptionalArguments() {
		$controller = $this->Router->match($this->mockRequest('router:foo:bar', '/router/foo/', null));
		$this->assertEquals('router:foo:bar', $controller);
	}

	public function testMatchWithSingleArgument() {
		$controller = $this->Router->match($this->mockRequest('router:foo', '/router/foo/', null));
		$this->assertEquals('router:foo', $controller);
	}

	public function testMatchWithoutArguments() {
		$controller = $this->Router->match($this->mockRequest('router', '/router/', null));
		$this->assertEquals('router', $controller);
	}

	public function testMatchWithDomain() {
		$controller = $this->Router->match($this->mockRequest('domain:router', '/router/', 'http://domain.test.com'));
		$this->assertEquals('domain:router', $controller);
	}

	public function testMatchQuery() {
		$Request = $this->getMock('moss\http\request\RequestInterface');

		$Request
			->expects($this->any())
			->method('getQuery')
			->will($this->returnValue('router'));

		$Request
			->expects($this->any())
			->method('controller')
			->will($this->returnValue('router'));

		$Request
			->expects($this->any())
			->method('baseName')
			->will($this->returnValue('http://test.com'));

		$Request
			->expects($this->any())
			->method('locale')
			->will($this->returnValue('fr'));

		$Request
			->expects($this->any())
			->method('format')
			->will($this->returnValue('yml'));

		$controller = $this->Router->match($Request);
		$this->assertEquals('router', $controller);
		$this->assertAttributeEquals(array('host' => 'http://test.com', 'controller' => 'router', 'locale' => 'fr', 'format' => 'yml'), 'defaults', $this->Router);
	}


	/**
	 * @expectedException \moss\router\RouterException
	 */
	public function testMakeWithoutDefaultController() {
		$this->Router->make();
	}

	public function testMakeWithDefaultController() {
		$this->Router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
		$this->assertEquals('./router/foo/123/', $this->Router->make(null, array('foo' => 'foo', 'bar' => 123)));
	}

	public function testMakeNormal() {
		$this->Router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
		$this->assertEquals('?controller=router_foo_bar&foo=foo&bar=123', $this->Router->make('router:foo:bar', array('foo' => 'foo', 'bar' => 123), true, false));
	}

	public function testMakeUnknown() {
		$this->Router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
		$this->assertEquals('?controller=router_foo_bar', $this->Router->make('router:foo:bar'));
	}

	public function testMakeByName() {
		$this->Router->match($this->mockRequest('router:foo:bar', '/router/foo/123/', 'http://test.com/'));
		$this->assertEquals('http://domain.test.com/router/', $this->Router->make('domain_router'));
	}

	public function testMakeByController() {
		$this->Router->match($this->mockRequest('router:foo:bar', '/router/foo/123/', 'http://test.com/'));
		$this->assertEquals('http://domain.test.com/router/', $this->Router->make('domain:router'));
	}

	public function testMakeRelative() {
		$this->Router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
		$this->assertEquals('./router/foo/123/', $this->Router->make('router:foo:bar', array('foo' => 'foo', 'bar' => 123), false, true));
	}

	public function testMakeAbsolute() {
		$this->Router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
		$this->assertEquals('./router/foo/123/', $this->Router->make('router:foo:bar', array('foo' => 'foo', 'bar' => 123), false, false));
	}

	public function testMakeAbsoluteWithQuery() {
		$this->Router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
		$this->assertEquals('./router/foo/123/?yada=yada', $this->Router->make('router:foo:bar', array('foo' => 'foo', 'bar' => 123, 'yada' => 'yada')));
	}

	public function testMakeWithoutOptionalArguments() {
		$this->Router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
		$this->assertEquals('./router/foo/', $this->Router->make('router:foo:bar', array('foo' => 'foo')));
	}

	public function testMakeWithOptionalArguments() {
		$this->Router->match($this->mockRequest('router:foo:bar', '/router/foo/123/'));
		$this->assertEquals('./router/foo/123/', $this->Router->make('router:foo:bar', array('foo' => 'foo', 'bar' => 123)));
	}

	public function testMakeWithHost() {
		$this->Router->match($this->mockRequest('router:foo:bar', '/router/foo/123/', 'http://test.com/'));
		$this->assertEquals('http://domain.test.com/router/', $this->Router->make('domain:router'));
	}
}
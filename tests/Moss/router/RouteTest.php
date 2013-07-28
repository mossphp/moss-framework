<?php
namespace moss\router;

class RouteTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Route
	 */
	protected $Route;

	protected $tArr;

	public function testPattern() {
		$Route = new Route('/{foo}/({bar})/', 'foo');
		$this->assertEquals('foo', $Route->controller());
	}

	public function testController() {
		$Route = new Route('/{foo}/({bar})/', 'foo');
		$this->assertEquals('foo', $Route->controller());
	}

	public function testRequirements() {
		$Route = new Route('/{foo}/({bar})/', 'foo');
		$this->assertEquals(array('foo' => '[a-z0-9-._]+', 'bar' => '[a-z0-9-._]*'), $Route->requirements());
	}

	public function testRequirementsSet() {
		$Route = new Route('/{foo}/({bar})/', 'foo');
		$this->assertEquals(array('foo' => 'foo', 'bar' => 'bar'), $Route->requirements(array('foo' => 'foo', 'bar' => 'bar')));
	}

	/**
	 * @expectedException \moss\router\RouteException
	 */
	public function testRequirementsMissing() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('donk' => 'donk'));
	}

	public function testArguments() {
		$Route = new Route('/{foo}/({bar})/', 'foo');
		$this->assertEquals(array('foo' => null), $Route->arguments());
	}

	public function testArgumentsSet() {
		$Route = new Route('/{foo}/({bar})/', 'foo');
		$this->assertEquals(array('foo' => 'foo', 'bar' => 'bar'), $Route->arguments(array('foo' => 'foo', 'bar' => 'bar')));
	}

	/**
	 * @expectedException \moss\router\RouteException
	 */
	public function testArgumentsMissing() {
		$Route = new Route('/{foo}/({bar})/', 'foo');
		$Route->arguments(array('yada' => 'yada'));
	}

	public function testMatch() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d+'));
		$this->assertTrue($Route->match($this->mockRequest('/foo/123/')));
		$this->assertEquals(array('foo' => 'foo', 'bar' => 123), $Route->arguments());
	}

	public function testMatchWithoutOptional() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$this->assertTrue($Route->match($this->mockRequest('/foo/')));
		$this->assertEquals(array('foo' => 'foo', 'bar' => null), $Route->arguments());
	}

	public function testMatchSchema() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$Route->schema('HTTP');
		$this->assertTrue($Route->match($this->mockRequest('/foo/', 'HTTP')));
		$this->assertFalse($Route->match($this->mockRequest('/foo/', 'FTP')));
	}

	public function testMatchMethod() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$Route->methods(array('get', 'POST'));
		$this->assertTrue($Route->match($this->mockRequest('/foo/', null, 'GET')));
		$this->assertFalse($Route->match($this->mockRequest('/foo/', null, 'PUT')));
	}

	public function testMatchHost() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$Route->host('foo.test.com');
		$this->assertTrue($Route->match($this->mockRequest('/foo/', null, null, 'http://foo.test.com')));
		$this->assertFalse($Route->match($this->mockRequest('/foo/', null, null, 'http://bar.test.com')));
	}

	public function testMatchHostWithBaseName() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$Route->host('foo.{basename}');
		$this->assertTrue($Route->match($this->mockRequest('/foo/', null, null, 'http://foo.test.com')));
		$this->assertFalse($Route->match($this->mockRequest('/foo/', null, null, 'http://bar.test.com')));
	}

	public function testMatchWrongUrl() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$this->assertFalse($Route->match($this->mockRequest('/')));
		$this->assertFalse($Route->match($this->mockRequest('/lorem/ipsum.html')));
	}

	public function testMatchInvalid() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$this->assertFalse($Route->match($this->mockRequest('/123/abc/')));
	}

	public function testCheckRequiredArgs() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$this->assertTrue($Route->check('foo', array('foo' => 'foo')));
	}

	public function testCheck() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$this->assertTrue($Route->check('foo', array('foo' => 'foo', 'bar' => 'bar')));
	}

	public function testCheckAdditionalArgs() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$this->assertTrue($Route->check('foo', array('foo' => 'foo', 'bar' => 123)));
	}

	public function testCheckInvalidController() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$this->assertFalse($Route->check('bar', array()));
	}

	public function testCheckInsufficientArgs() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$this->assertFalse($Route->check('foo', array()));
	}

	public function testCheckInvalidArgs() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$this->assertFalse($Route->check('foo', array('foo' => '---')));
	}

	public function testMakeRelative() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$this->assertEquals('./foo/123/', $Route->make('http://localhost/', array('foo' => 'foo', 'bar' => '123'), false));
	}

	public function testMakeEmptyBasename() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$this->assertEquals('./foo/123/', $Route->make(null, array('foo' => 'foo', 'bar' => '123'), true));
	}

	/**
	 * @expectedException \moss\router\RouteException
	 */
	public function testMakeEmptyBasenameWithDomain() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$Route->host('domain.{basename}');

		$Route->make(null, array('foo' => 'foo', 'bar' => '123'), true);
	}

	public function testMakeAbsolute() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$this->assertEquals('http://localhost/foo/123/', $Route->make('http://localhost/', array('foo' => 'foo', 'bar' => '123'), true));
	}

	public function testMakeOnlyRequired() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$this->assertEquals('http://localhost/foo/', $Route->make('http://localhost/', array('foo' => 'foo')));
	}

	public function testMakeWithQuery() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$this->assertEquals('http://localhost/foo/123/?yada=yada', $Route->make('http://localhost/', array('foo' => 'foo', 'bar' => '123', 'yada' => 'yada')));
	}

	public function testMakeSubdomain() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$Route->host('foo.{basename}');
		$this->assertEquals('http://foo.localhost/foo/', $Route->make('http://localhost/', array('foo' => 'foo')));
	}

	/**
	 * @expectedException \moss\router\RouteException
	 */
	public function testMakeInsufficientArg() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$Route->make('http://localhost/', array());
	}

	/**
	 * @expectedException \moss\router\RouteException
	 */
	public function testMakeInvalidArg() {
		$Route = new Route('/{foo}/({bar})/', 'foo', array('foo' => '\w+', 'bar' => '\d*'));
		$Route->make('http://localhost/', array('foo' => 'foo', 'bar' => 'bar'));
	}


	protected function mockRequest($url, $schema = null, $method = null, $host = null) {
		$Request = $this->getMock('moss\http\request\RequestInterface');
		$Request
			->expects($this->any())
			->method('url')
			->will($this->returnValue($url));

		$Request
			->expects($this->any())
			->method('schema')
			->will($this->returnValue($schema));

		$Request
			->expects($this->any())
			->method('method')
			->will($this->returnValue($method));

		$Request
			->expects($this->any())
			->method('host')
			->will($this->returnValue($host));

		return $Request;
	}


}
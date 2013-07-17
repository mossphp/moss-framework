<?php
namespace Moss\http\response;


class ResponseTest extends \PHPUnit_Framework_TestCase {


	public function testScalarContent() {
		$Response = new Response('Foo');
		$this->assertEquals('Foo', $Response->content());
	}

	public function testObjectContent() {
		$Response = new Response(new \SplFileInfo(__FILE__));
		$this->assertEquals(__FILE__, $Response->content());
	}

	/**
	 * @expectedException \Moss\http\response\ResponseException
	 */
	public function testInvaliudContent() {
		new Response(array());
	}

	public function testValidStatus() {
		$Response = new Response('Foo', 200);
		$this->assertEquals(200, $Response->status());
	}

	/**
	 * @expectedException \Moss\http\response\ResponseException
	 */
	public function testInvalidStatus() {
		new Response('Foo', 999);
	}
}

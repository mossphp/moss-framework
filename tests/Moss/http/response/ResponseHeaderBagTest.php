<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Michal
 * Date: 16.07.13
 * Time: 21:16
 * To change this template use File | Settings | File Templates.
 */

namespace Moss\http\response;


class ResponseHeaderBagTest extends \PHPUnit_Framework_TestCase {

	public function testHeader() {
		$HeaderBag = $this->getMockForAbstractClass('\Moss\http\response\ResponseHeaderBag');
		$this->assertFalse($HeaderBag->hasHeader('Content-Type'));
		$this->assertNull($HeaderBag->getHeader('Content-Type'));

		$HeaderBag->addHeader('Content-Type', 'text/plain');
		$this->assertTrue($HeaderBag->hasHeader('Content-Type'));
		$this->assertEquals('text/plain', $HeaderBag->getHeader('Content-Type'));

		$HeaderBag->removeHeader('Content-Type');

		$this->assertFalse($HeaderBag->hasHeader('Content-Type'));
		$this->assertNull($HeaderBag->getHeader('Content-Type'));
	}


	public function testHeaders() {
		$HeaderBag = $this->getMockForAbstractClass('\Moss\http\response\ResponseHeaderBag');
		$this->assertFalse($HeaderBag->hasHeader('Content-Type'));
		$this->assertNull($HeaderBag->getHeader('Content-Type'));

		$HeaderBag->setHeaders(array('Content-Type' => 'text/plain'));
		$this->assertEquals(array('Content-Type' => 'text/plain'), $HeaderBag->getHeaders());

		$this->assertTrue($HeaderBag->hasHeader('Content-Type'));
		$this->assertEquals('text/plain', $HeaderBag->getHeader('Content-Type'));
	}

}

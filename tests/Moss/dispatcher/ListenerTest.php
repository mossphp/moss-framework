<?php
namespace Moss\dispatcher;


class ListenerTest extends \PHPUnit_Framework_TestCase {

	public function testGetNoArgs() {
		$Container = $this->getMock('\Moss\container\ContainerInterface');
		$Container
			->expects($this->any())
			->method('get')
			->will($this->returnValue(new \tests\Moss\Foobar()));

		$Listener = new Listener('\tests\Moss\Foobar', 'foo', array());
		$this->assertEquals(new \tests\Moss\Foobar(), $Listener->get($Container));
	}

	public function testGetNormalArgs() {
		$Container = $this->getMock('\Moss\container\ContainerInterface');
		$Container
			->expects($this->any())
			->method('get')
			->will($this->returnValue(new \tests\Moss\Foobar()));

		$Listener = new Listener('\tests\Moss\Foobar', 'foo', array('foo', 'bar', array('y', 'a', 'd', 'a')));
		$this->assertEquals(new \tests\Moss\Foobar('foo', 'bar', array('y', 'a', 'd', 'a')), $Listener->get($Container));
	}

	public function testGetSpecial() {
		$Container = $this->getMock('\Moss\container\ContainerInterface');
		$Container
			->expects($this->any())
			->method('get')
			->will($this->returnValue(new \tests\Moss\Foobar()));

		$Listener = new Listener('\tests\Moss\Foobar', 'foo', array('@Subject', '@Message'));
		$this->assertEquals(new \tests\Moss\Foobar('Subject', 'Message'), $Listener->get($Container, 'Subject', 'Message'));
	}

	public function testGetContainerArgs() {
		$Container = $this->getMock('\Moss\container\ContainerInterface');
		$Container
			->expects($this->at(0))
			->method('get')
			->will($this->returnValue(new \tests\Moss\Foobar()));

		$Container
			->expects($this->at(1))
			->method('get')
			->will($this->returnValue(new \stdClass()));

		$Listener = new Listener('\tests\Moss\Foobar', 'foo', array('@Foobar'));
		$this->assertEquals(new \tests\Moss\Foobar(new \stdClass()), $Listener->get($Container));
	}
}

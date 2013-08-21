<?php
namespace moss\dispatcher;


class ListenerTest extends \PHPUnit_Framework_TestCase {

	public function testGetNoArgs() {
		$Container = $this->getMock('\moss\container\ContainerInterface');
		$Container
			->expects($this->any())
			->method('get')
			->will($this->returnValue(new \tests\moss\Foobar()));

		$Listener = new Listener('\tests\moss\Foobar', 'foo', array());
		$this->assertEquals(new \tests\moss\Foobar(), $Listener->get($Container));
	}

	public function testGetNormalArgs() {
		$Container = $this->getMock('\moss\container\ContainerInterface');
		$Container
			->expects($this->any())
			->method('get')
			->will($this->returnValue(new \tests\moss\Foobar()));

		$Listener = new Listener('\tests\moss\Foobar', 'foo', array('foo', 'bar', array('y', 'a', 'd', 'a')));
		$this->assertEquals(new \tests\moss\Foobar('foo', 'bar', array('y', 'a', 'd', 'a')), $Listener->get($Container));
	}

	public function testGetSpecial() {
		$Container = $this->getMock('\moss\container\ContainerInterface');
		$Container
			->expects($this->any())
			->method('get')
			->will($this->returnValue(new \tests\moss\Foobar()));

		$Listener = new Listener('\tests\moss\Foobar', 'foo', array('@Subject', '@Message'));
		$this->assertEquals(new \tests\moss\Foobar('Subject', 'Message'), $Listener->get($Container, 'Subject', 'Message'));
	}

	public function testGetContainerArgs() {
		$Container = $this->getMock('\moss\container\ContainerInterface');
		$Container
			->expects($this->at(0))
			->method('get')
			->will($this->returnValue(new \tests\moss\Foobar()));

		$Container
			->expects($this->at(1))
			->method('get')
			->will($this->returnValue(new \stdClass()));

		$Listener = new Listener('\tests\moss\Foobar', 'foo', array('@Foobar', '@Container'));
		$this->assertEquals(new \tests\moss\Foobar(new \stdClass(), $Container), $Listener->get($Container));
	}
}

<?php
namespace Moss\container;


class ComponentTest extends \PHPUnit_Framework_TestCase {

	public function testGetNoArgs() {
		$Component = new Component('\tests\Moss\Foobar', array());
		$this->assertEquals(new \tests\Moss\Foobar, $Component->get());
	}

	public function testSimpleArgs() {
		$Component = new Component('\tests\Moss\Foobar', array('foo', 'bar', array('y','a','d','a')));
		$this->assertEquals(new \tests\Moss\Foobar('foo', 'bar', array('y','a','d','a')), $Component->get());
	}

	/**
	 * @expectedException \Moss\container\ContainerException
	 */
	public function testComponentArgs() {
		$Component = new Component('\tests\Moss\Foobar', array('@foo', '@bar', '@yada'));
		$this->assertEquals(new \tests\Moss\Foobar, $Component->get());
	}

	public function testComponentMethods() {
		$Component = new Component('\tests\Moss\Foobar', array(), array('foo' => array('foo', 'bar', 'yada')));
		$this->assertAttributeEquals(array('foo', 'bar', 'yada'), 'args', $Component->get());
	}
}

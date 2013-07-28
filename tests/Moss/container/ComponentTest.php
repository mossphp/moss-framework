<?php
namespace moss\container;


class ComponentTest extends \PHPUnit_Framework_TestCase {

	public function testGetNoArgs() {
		$Component = new Component('\tests\moss\Foobar', array());
		$this->assertEquals(new \tests\moss\Foobar, $Component->get());
	}

	public function testSimpleArgs() {
		$Component = new Component('\tests\moss\Foobar', array('foo', 'bar', array('y','a','d','a')));
		$this->assertEquals(new \tests\moss\Foobar('foo', 'bar', array('y','a','d','a')), $Component->get());
	}

	/**
	 * @expectedException \moss\container\ContainerException
	 */
	public function testComponentArgs() {
		$Component = new Component('\tests\moss\Foobar', array('@foo', '@bar', '@yada'));
		$this->assertEquals(new \tests\moss\Foobar, $Component->get());
	}

	public function testComponentMethods() {
		$Component = new Component('\tests\moss\Foobar', array(), array('foo' => array('foo', 'bar', 'yada')));
		$this->assertAttributeEquals(array('foo', 'bar', 'yada'), 'args', $Component->get());
	}
}

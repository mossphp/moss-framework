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
	public function testComponentArgsWithoutContainer() {
		$Component = new Component('\tests\moss\Foobar', array('@foo', '@bar', '@yada'));
		$this->assertEquals(new \tests\moss\Foobar, $Component->get());
	}

	public function testComponentArgsWithContainer() {
		$Container = $this->getMock('\moss\container\ContainerInterface');
		$Container->expects($this->any())->method($this->anything())->will($this->returnValue('foo'));

		$Component = new Component('\tests\moss\Foobar', array('@foo', '@bar', '@yada', '@Container'));
		$this->assertEquals(new \tests\moss\Foobar('foo', 'foo', 'foo', $Container), $Component->get($Container));
	}

	public function testComponentMethods() {
		$Component = new Component('\tests\moss\Foobar', array(), array('foo' => array('foo', 'bar', 'yada')));
		$this->assertAttributeEquals(array('foo', 'bar', 'yada'), 'args', $Component->get());
	}
}

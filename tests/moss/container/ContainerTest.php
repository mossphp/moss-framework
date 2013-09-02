<?php
namespace moss\container;

class ContainerTest extends \PHPUnit_Framework_TestCase {


	public function testValue() {
		$Container = new Container();
		$Container->register('foo', 'bar');
		$this->assertEquals('bar', $Container->get('foo'));
	}

	/**
	 * @expectedException \moss\container\ContainerException
	 */
	public function testInvalidValue() {
		$Container = new Container();
		$Container->register('foo', 'bar');
		$Container->get('foo.bar');
	}

	public function testDeepValueFromString() {
		$Container = new Container();
		$Container->register('foo.bar', 'yada');
		$this->assertEquals(array('bar' => 'yada'), $Container->get('foo'));
		$this->assertEquals('yada', $Container->get('foo.bar'));
	}

	public function testDeepValueFromArray() {
		$Container = new Container();
		$Container->register('foo', array('bar' => 'yada'));
		$this->assertEquals(array('bar' => 'yada'), $Container->get('foo'));
		$this->assertEquals('yada', $Container->get('foo.bar'));
	}

	public function testClosure() {
		$Container = new Container();
		$Container->register('foo', function () {
			return new \stdClass();
		}, false);

		$this->assertEquals(new \stdClass(), $Container->get('foo'));
	}

	public function testSharedClosure() {
		$Container = new Container();
		$Container->register('foo', function () {
			return new \stdClass();
		}, true);

		$obj = $Container->get('foo');
		$obj->foo = 123;
		$this->assertEquals($obj, $Container->get('foo'));
	}

	public function testDefinition() {
		$component = $this->getMock('\moss\container\ComponentInterface');
		$component
			->expects($this->any())
			->method('get')
			->will($this->returnValue(new \stdClass()));

		$Container = new Container();
		$Container->register('foo', $component, false);

		$this->assertEquals(new \stdClass(), $Container->get('foo'));
	}

	public function testSharedDefinition() {
		$component = $this->getMock('\moss\container\ComponentInterface');
		$component
			->expects($this->any())
			->method('get')
			->will($this->returnValue(new \stdClass()));

		$Container = new Container();
		$Container->register('foo', $component, true);

		$obj = $Container->get('foo');
		$obj->foo = 123;
		$this->assertEquals($obj, $Container->get('foo'));
	}

	public function testInstance() {
		$Container = new Container();
		$Container->register('foo', new \stdClass(), false);

		$obj = $Container->get('foo');
		$obj->foo = 123;
		$this->assertEquals($obj, $Container->get('foo'));
	}

	public function testUnregister() {
		$Container = new Container();
		$Container->register('foo', 'bar', true);
		$this->assertTrue($Container->isShared('foo'));
		$Container->Unregister('foo');
		$this->assertFalse($Container->exists('foo'));
	}

	public function testUnregisterShared() {
		$Container = new Container();
		$result = $Container->register('foo', 'bar', true);
		$this->assertTrue($Container->isShared('foo'));
		$Container->Unregister('foo');
		$this->assertFalse($Container->exists('foo'));
	}

	public function testUnregisterInstance() {
		$Container = new Container();
		$result = $Container->register('foo', new \stdClass());
		$this->assertTrue($Container->isShared('foo'));
		$Container->Unregister('foo');
		$this->assertFalse($Container->exists('foo'));
	}
}

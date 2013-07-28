<?php
namespace moss\container;

class ContainerTest extends \PHPUnit_Framework_TestCase {

	/** @var Container */
	protected $Container;

	public function setUp() {
		$this->Container = new Container();
	}

	public function testRegisterDefinition() {
		$result = $this->Container->register('foo', 'bar', false);

		$this->assertInstanceOf('moss\container\Container', $result);
		$this->assertAttributeCount(1, 'components', $this->Container);
	}

	public function testRegisterSharedDefinition() {
		$result = $this->Container->register('foo', 'bar', true);

		$this->assertInstanceOf('moss\container\Container', $result);
		$this->assertAttributeCount(1, 'components', $this->Container);
		$this->assertAttributeCount(1, 'instances', $this->Container);
	}

	public function testRegisterInstance() {
		$result = $this->Container->register('foo', new \stdClass());

		$this->assertInstanceOf('moss\container\Container', $result);
		$this->assertAttributeCount(0, 'components', $this->Container);
		$this->assertAttributeCount(1, 'instances', $this->Container);
	}

	public function testUnRegisterDefinition() {
		$result = $this->Container->register('foo', 'bar', false);

		$this->assertInstanceOf('moss\container\Container', $result);
		$this->assertAttributeCount(1, 'components', $this->Container);

		$result = $this->Container->unregister('foo');

		$this->assertInstanceOf('moss\container\Container', $result);
		$this->assertAttributeCount(0, 'components', $this->Container);
	}

	public function testUnRegisterSharedDefinition() {
		$result = $this->Container->register('foo', 'bar', true);

		$this->assertInstanceOf('moss\container\Container', $result);
		$this->assertAttributeCount(1, 'components', $this->Container);
		$this->assertAttributeCount(1, 'instances', $this->Container);

		$result = $this->Container->unregister('foo');

		$this->assertInstanceOf('moss\container\Container', $result);
		$this->assertAttributeCount(0, 'components', $this->Container);
		$this->assertAttributeCount(0, 'instances', $this->Container);
	}

	public function testUnRegisterInstance() {
		$result = $this->Container->register('foo', new \stdClass());

		$this->assertInstanceOf('moss\container\Container', $result);
		$this->assertAttributeCount(0, 'components', $this->Container);
		$this->assertAttributeCount(1, 'instances', $this->Container);

		$result = $this->Container->unregister('foo');

		$this->assertInstanceOf('moss\container\Container', $result);
		$this->assertAttributeCount(0, 'components', $this->Container);
		$this->assertAttributeCount(0, 'instances', $this->Container);
	}

	public function testDefinitionExists() {
		$this->assertFalse($this->Container->exists('foo'));

		$this->Container->register('foo', 'bar', false);

		$this->assertTrue($this->Container->exists('foo'));
	}

	public function testSharedDefinitionExists() {
		$this->assertFalse($this->Container->exists('foo'));

		$this->Container->register('foo', 'bar', true);

		$this->assertTrue($this->Container->exists('foo'));
	}

	public function testInstanceExists() {
		$this->assertFalse($this->Container->exists('foo'));

		$this->Container->register('foo', new \stdClass());

		$this->assertTrue($this->Container->exists('foo'));
	}

	public function testNotSharedDefinition() {
		$this->Container->register('foo', 'bar', false);

		$this->assertFalse($this->Container->isShared('foo'));
	}

	public function testSharedDefinition() {
		$this->Container->register('foo', 'bar', true);

		$this->assertTrue($this->Container->isShared('foo'));
	}

	public function testSharedInstance() {
		$this->Container->register('foo', new \stdClass());

		$this->assertTrue($this->Container->isShared('foo'));
	}

	public function testGetValue() {
		$this->Container->register('foo', 'bar', false);

		$this->assertEquals('bar', $this->Container->get('foo'));
	}

	public function testGetSharedValue() {
		$this->Container->register('foo', 'bar', true);

		$this->assertEquals('bar', $this->Container->get('foo'));
	}

	public function testGetClosure() {
		$this->Container->register('foo', function () {
			return 'bar';
		}, false);

		$this->assertEquals('bar', $this->Container->get('foo'));
	}

	public function testGetSharedClosure() {
		$this->Container->register('foo', function () {
			return 'bar';
		}, true);

		$this->assertEquals('bar', $this->Container->get('foo'));
	}

	public function testGetComponent() {
		$this->Container->register('foo', new Component('\moss\container\Component', array('\stdClass')), false);
		$this->assertEquals(new Component('\stdClass'), $this->Container->get('foo'));
	}

	public function testGetSharedComponent() {
		$this->Container->register('foo', new Component('\moss\container\Component', array('\stdClass')), true);
		$this->assertEquals(new Component('\stdClass'), $this->Container->get('foo'));
	}

	public function testGetInstance() {
		$this->Container->register('foo', new \stdClass());

		$this->assertEquals(new \stdClass(), $this->Container->get('foo'));
	}

	/**
	 * @expectedException \moss\container\ContainerException
	 */
	public function testGetUndefined() {
		$this->Container->get('foo');
	}

	public function testDefinitionSharing() {
		$this->Container->register('foo', 1, true);

		$v = & $this->Container->get('foo');
		$v++;

		$this->assertEquals(2, $this->Container->get('foo'));
	}

	public function testComponentSharing() {
		$this->Container->register('foo', new Component('\stdClass'), true);

		$v = $this->Container->get('foo');
		$v->v = 2;

		$this->assertAttributeEquals(2, 'v', $this->Container->get('foo'));
	}

	public function testInstanceSharing() {
		$this->Container->register('foo', new \stdClass());

		$v = $this->Container->get('foo');
		$v->v = 2;

		$this->assertAttributeEquals(2, 'v', $this->Container->get('foo'));
	}
}

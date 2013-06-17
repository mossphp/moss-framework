<?php
namespace Moss\dispatcher;


class DispatcherTest extends \PHPUnit_Framework_TestCase {

	/** @var Dispatcher */
	protected $Dispatcher;

	public function setUp() {
		$this->Dispatcher = new Dispatcher();
	}

	public function testRegisterSingleEvent() {
		$result = $this->Dispatcher->register('foo', function () {
		});

		$this->assertInstanceOf('Moss\dispatcher\Dispatcher', $result);
		$this->assertAttributeCount(1, 'events', $this->Dispatcher);
	}

	public function testRegisterMultipleEvents() {
		$result = $this->Dispatcher->register(array('foo', 'bar'), function () {
		});

		$this->assertInstanceOf('Moss\dispatcher\Dispatcher', $result);
		$this->assertAttributeCount(2, 'events', $this->Dispatcher);
	}

	public function testRegisterPriority() {
		$result = $this->Dispatcher
			->register('foo', function () {}, 1)
			->register('foo', function () {}, 0);


		$this->assertInstanceOf('Moss\dispatcher\Dispatcher', $result);
		$this->assertAttributeCount(1, 'events', $this->Dispatcher);
	}

	public function testFireEmptyEvent() {
		$this->assertNull($this->Dispatcher->fire('foo'));
	}

	public function testFireEvent() {
		$this->Dispatcher->register('foo', function () { return 'foo'; });
		$this->assertEquals('foo', $this->Dispatcher->fire('foo'));
	}

	public function testFireBefore() {
		$this->Dispatcher->register('foo:before', function () { return 'foo'; });
		$this->assertEquals('foo', $this->Dispatcher->fire('foo'));
	}

	public function testFireAfter() {
		$this->Dispatcher->register('foo:after', function () { return 'foo'; });
		$this->assertEquals('foo', $this->Dispatcher->fire('foo'));
	}

	/**
	 * @expectedException \Exception
	 */
	public function testFireException() {
		$this->Dispatcher->register('foo', function () { throw new \Exception('forced'); });
		$this->assertEquals('foo', $this->Dispatcher->fire('foo'));
	}

	public function testFireHandleException() {
		$this->Dispatcher->register('foo', function () { throw new \Exception('forced'); });
		$this->Dispatcher->register('foo:exception', function () { return 'foo'; });
		$this->assertEquals('foo', $this->Dispatcher->fire('foo'));
	}

	public function testFire() {
		$this->Dispatcher->register('foo:before', function ($Container, $Subject) { return $Subject.':before'; });
		$this->Dispatcher->register('foo', function ($Container, $Subject) { return $Subject.':event'; });
		$this->Dispatcher->register('foo:after', function ($Container, $Subject) { return $Subject.':after'; });
		$this->assertEquals('Subject:before:event:after', $this->Dispatcher->fire('foo', 'Subject'));
	}
}

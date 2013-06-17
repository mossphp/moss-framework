<?php
namespace Moss\http\session;

/**
 * @package Moss Test
 */
class SessionTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @var Session
	 */
	protected $object;

	protected function setUp() {
		$this->object = new Session();
		$this->object->offsetSet('foo', 'bar');
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testReset() {
		$this->object->reset();
		$this->assertEquals(0, $this->object->count());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testOffsetUnset() {
		$this->object->offsetUnset('foo');
		$this->assertEquals(0, $this->object->count());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testOffsetSet() {
		$this->assertEquals('bar', $this->object['foo']);
		$this->assertEquals('bar', $_SESSION['foo']);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testOffsetGet() {
		$this->assertEquals('bar', $this->object['foo']);
		$this->assertEquals('bar', $_SESSION['foo']);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testOffsetExists() {
		$this->assertTrue(isset($this->object['foo']));
		$this->assertTrue(isset($_SESSION['foo']));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testCurrent() {
		$this->assertEquals(current($_SESSION), $this->object->current());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testNext() {
		$prev = current($_SESSION);
		$this->object->next();
		$this->assertEquals(current($_SESSION), $this->object->current());
		$this->assertNotEquals($prev, current($_SESSION));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testKey() {
		$this->assertEquals(key($_SESSION), $this->object->key());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testValid() {
		$this->object->rewind();
		$this->assertTrue($this->object->valid());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRewind() {
		$this->object->rewind();
		$this->assertEquals(reset($_SESSION), $this->object->current());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testCount() {
		$this->assertEquals(count($_SESSION), $this->object->count()+1);
	}
}

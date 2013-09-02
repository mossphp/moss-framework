<?php
namespace moss\http\session;

/**
 * @package Moss Test
 */
class SessionTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @runInSeparateProcess
	 */
	public function testReset() {
		$Session = new Session();
		$Session->offsetSet('foo', 'bar');
		$Session->reset();
		$this->assertEquals(0, $Session->count());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testOffsetUnset() {
		$Session = new Session();
		$Session->offsetSet('foo', 'bar');
		$Session->offsetUnset('foo');
		$this->assertEquals(0, $Session->count());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testOffsetSet() {
		$Session = new Session();
		$Session->offsetSet('foo', 'bar');
		$this->assertEquals('bar', $Session['foo']);
		$this->assertEquals('bar', $_SESSION['foo']);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testOffsetGet() {
		$Session = new Session();
		$Session->offsetSet('foo', 'bar');
		$this->assertEquals('bar', $Session['foo']);
		$this->assertEquals('bar', $_SESSION['foo']);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testOffsetExists() {
		$Session = new Session();
		$Session->offsetSet('foo', 'bar');
		$this->assertTrue(isset($Session['foo']));
		$this->assertTrue(isset($_SESSION['foo']));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testCurrent() {
		$Session = new Session();
		$Session->offsetSet('foo', 'bar');
		$this->assertEquals(current($_SESSION), $Session->current());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testNext() {
		$Session = new Session();
		$Session->offsetSet('foo', 'bar');
		$prev = current($_SESSION);
		$Session->next();
		$this->assertEquals(current($_SESSION), $Session->current());
		$this->assertNotEquals($prev, current($_SESSION));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testKey() {
		$Session = new Session();
		$Session->offsetSet('foo', 'bar');
		$this->assertEquals(key($_SESSION), $Session->key());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testValid() {
		$Session = new Session();
		$Session->offsetSet('foo', 'bar');
		$Session->rewind();
		$this->assertTrue($Session->valid());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRewind() {
		$Session = new Session();
		$Session->offsetSet('foo', 'bar');
		$Session->rewind();
		$this->assertEquals(reset($_SESSION), $Session->current());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testCount() {
		$Session = new Session();
		$Session->offsetSet('foo', 'bar');
		$this->assertEquals(count($_SESSION), $Session->count()+1);
	}
}

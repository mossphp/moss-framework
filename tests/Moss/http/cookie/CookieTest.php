<?php
namespace moss\http\cookie;

/**
 * @package Moss Test
 */
class CookieTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @runInSeparateProcess
	 */
	public function testReset() {
		$Cookie = new Cookie();
		$Cookie->offsetSet('foo', 'bar');
		$Cookie->reset();
		$this->assertEquals(0, $Cookie->count());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testOffsetUnset() {
		$Cookie = new Cookie();
		$Cookie->offsetSet('foo', 'bar');
		$Cookie->offsetUnset('foo');
		$this->assertEquals(0, $Cookie->count());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testOffsetSet() {
		$Cookie = new Cookie();
		$Cookie->offsetSet('foo', 'bar');
		$this->assertEquals('bar', $Cookie['foo']);
		$this->assertEquals('bar', $_COOKIE['foo']);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testOffsetGet() {
		$Cookie = new Cookie();
		$Cookie->offsetSet('foo', 'bar');
		$this->assertEquals('bar', $Cookie['foo']);
		$this->assertEquals('bar', $_COOKIE['foo']);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testOffsetExists() {
		$Cookie = new Cookie();
		$Cookie->offsetSet('foo', 'bar');
		$this->assertTrue(isset($Cookie['foo']));
		$this->assertTrue(isset($_COOKIE['foo']));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testCurrent() {
		$Cookie = new Cookie();
		$Cookie->offsetSet('foo', 'bar');
		$this->assertEquals(current($_COOKIE), $Cookie->current());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testNext() {
		$Cookie = new Cookie();
		$Cookie->offsetSet('foo', 'bar');
		$prev = current($_COOKIE);
		$Cookie->next();
		$this->assertEquals(current($_COOKIE), $Cookie->current());
		$this->assertNotEquals($prev, current($_COOKIE));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testKey() {
		$Cookie = new Cookie();
		$Cookie->offsetSet('foo', 'bar');
		$this->assertEquals(key($_COOKIE), $Cookie->key());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testValid() {
		$Cookie = new Cookie();
		$Cookie->offsetSet('foo', 'bar');
		$Cookie->rewind();
		$this->assertTrue($Cookie->valid());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRewind() {
		$Cookie = new Cookie();
		$Cookie->offsetSet('foo', 'bar');
		$Cookie->rewind();
		$this->assertEquals(reset($_COOKIE), $Cookie->current());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testCount() {
		$Cookie = new Cookie();
		$Cookie->offsetSet('foo', 'bar');
		$this->assertEquals(count($_COOKIE), $Cookie->count()+1);
	}
}

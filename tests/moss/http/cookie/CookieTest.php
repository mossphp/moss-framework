<?php
namespace moss\http\cookie;

/**
 * @package Moss Test
 */
class CookieTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @runInSeparateProcess
     */
    public function testGetSet()
    {
        $cookie = new Cookie();
        $cookie->set('foo', 'bar');
        $this->assertEquals($_COOKIE['foo'], $cookie->get('foo'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRemove()
    {
        $cookie = new Cookie();
        $cookie->set('foo', 'bar');
        $this->assertEquals($_COOKIE['foo'], $cookie->get('foo'));
        $cookie->remove('foo');
        $this->assertArrayNotHasKey('foo', $_COOKIE);
        $this->assertNull($cookie->get('foo'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testAll()
    {
        $cookie = new Cookie();
        $cookie->set('foo', 'bar');
        $cookie->set('yada', 'yada');
        $this->assertEquals(array('foo' => 'bar', 'yada' => 'yada'), $cookie->all());
    }

    /**
     * @runInSeparateProcess
     */
    public function testReset()
    {
        $cookie = new Cookie();
        $cookie->set('foo', 'bar');
        $cookie->set('yada', 'yada');
        $this->assertEquals(2, $cookie->count());
        $cookie->reset();
        $this->assertEquals(0, $cookie->count());
    }

    /**
     * @runInSeparateProcess
     */
    public function testOffsetUnset()
    {
        $cookie = new Cookie();
        $cookie->offsetSet('foo', 'bar');
        $cookie->offsetUnset('foo');
        $this->assertEquals(0, $cookie->count());
    }

    /**
     * @runInSeparateProcess
     */
    public function testOffsetSet()
    {
        $cookie = new Cookie();
        $cookie->offsetSet('foo', 'bar');
        $this->assertEquals('bar', $cookie->get('foo'));
        $this->assertEquals('bar', $cookie['foo']);
        $this->assertEquals('bar', $_COOKIE['foo']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testOffsetGet()
    {
        $cookie = new Cookie();
        $cookie->offsetSet('foo', 'bar');
        $this->assertEquals('bar', $cookie['foo']);
        $this->assertEquals('bar', $_COOKIE['foo']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testOffsetExists()
    {
        $cookie = new Cookie();
        $cookie->offsetSet('foo', 'bar');
        $this->assertTrue(isset($cookie['foo']));
        $this->assertTrue(isset($_COOKIE['foo']));
    }

    /**
     * @runInSeparateProcess
     */
    public function testCurrent()
    {
        $cookie = new Cookie();
        $cookie->offsetSet('foo', 'bar');
        $this->assertEquals(current($_COOKIE), $cookie->current());
    }

    /**
     * @runInSeparateProcess
     */
    public function testNext()
    {
        $cookie = new Cookie();
        $cookie->offsetSet('foo', 'bar');
        $prev = current($_COOKIE);
        $cookie->next();
        $this->assertEquals(current($_COOKIE), $cookie->current());
        $this->assertNotEquals($prev, current($_COOKIE));
    }

    /**
     * @runInSeparateProcess
     */
    public function testKey()
    {
        $cookie = new Cookie();
        $cookie->offsetSet('foo', 'bar');
        $this->assertEquals(key($_COOKIE), $cookie->key());
    }

    /**
     * @runInSeparateProcess
     */
    public function testValid()
    {
        $cookie = new Cookie();
        $cookie->offsetSet('foo', 'bar');
        $cookie->rewind();
        $this->assertTrue($cookie->valid());
    }

    /**
     * @runInSeparateProcess
     */
    public function testRewind()
    {
        $cookie = new Cookie();
        $cookie->offsetSet('foo', 'bar');
        $cookie->rewind();
        $this->assertEquals(reset($_COOKIE), $cookie->current());
    }

    /**
     * @runInSeparateProcess
     */
    public function testCount()
    {
        $cookie = new Cookie();
        $cookie->offsetSet('foo', 'bar');
        $this->assertEquals(count($_COOKIE), $cookie->count());
    }
}

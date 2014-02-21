<?php
namespace Moss\Http\Cookie;

class MockCookie extends Cookie
{
    public function __construct($domain = null, $path = '/', $Httponly = true, $ttl = 5356800)
    {
        if ($domain === null) {
            $domain = empty($_SERVER['HTTP_HOST']) ? null : $_SERVER['HTTP_HOST'];
        }

        $this->domain = $domain;
        $this->path = $path;
        $this->Httponly = $Httponly;
        $this->expire = microtime(true) + $ttl;

        $this->storage = array();
    }

    protected function setcookie($name, $value, $expire = 0)
    {
        return;
    }
}

/**
 * @package Moss Test
 */
class CookieTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSet()
    {
        $cookie = new MockCookie();
        $cookie->set('foo', 'bar');
        $this->assertEquals('bar', $cookie->get('foo'));
    }

    public function testRemove()
    {
        $cookie = new MockCookie();
        $cookie->set('foo', 'bar');
        $this->assertEquals('bar', $cookie->get('foo'));
        $cookie->remove('foo');
        $this->assertNull($cookie->get('foo'));
    }

    public function testAll()
    {
        $cookie = new MockCookie();
        $cookie->set('foo', 'bar');
        $cookie->set('yada', 'yada');
        $this->assertEquals(array('foo' => 'bar', 'yada' => 'yada'), $cookie->all());
    }

    public function testReset()
    {
        $cookie = new MockCookie();
        $cookie->set('foo', 'bar');
        $cookie->set('yada', 'yada');
        $this->assertEquals(2, $cookie->count());
        $cookie->reset();
        $this->assertEquals(0, $cookie->count());
    }

    public function testOffsetUnset()
    {
        $cookie = new MockCookie();
        $cookie->offsetSet('foo', 'bar');
        $cookie->offsetUnset('foo');
        $this->assertEquals(0, $cookie->count());
    }

    public function testOffsetSet()
    {
        $cookie = new MockCookie();
        $cookie->offsetSet('foo', 'bar');
        $this->assertEquals('bar', $cookie->get('foo'));
        $this->assertEquals('bar', $cookie['foo']);
    }

    public function testOffsetGet()
    {
        $cookie = new MockCookie();
        $cookie->offsetSet('foo', 'bar');
        $this->assertEquals('bar', $cookie['foo']);
    }

    public function testOffsetExists()
    {
        $cookie = new MockCookie();
        $cookie->offsetSet('foo', 'bar');
        $this->assertTrue(isset($cookie['foo']));
    }

    public function testCurrent()
    {
        $cookie = new MockCookie();
        $cookie->offsetSet('foo', 'bar');
        $this->assertEquals('bar', $cookie->current());
    }

    public function testNext()
    {
        $cookie = new MockCookie();
        $cookie->offsetSet('foo', 'bar');
        $cookie->next();
        $this->assertFalse($cookie->current());
    }

    public function testKey()
    {
        $cookie = new MockCookie();
        $cookie->offsetSet('foo', 'bar');
        $this->assertEquals('foo', $cookie->key());
    }

    public function testValid()
    {
        $cookie = new MockCookie();
        $cookie->offsetSet('foo', 'bar');
        $cookie->rewind();
        $this->assertTrue($cookie->valid());
    }

    public function testRewind()
    {
        $cookie = new MockCookie();
        $cookie->offsetSet('foo', 'bar');
        $cookie->rewind();
        $this->assertEquals('bar', $cookie->current());
    }

    public function testCount()
    {
        $cookie = new MockCookie();
        $cookie->offsetSet('foo', 'bar');
        $this->assertEquals(1, $cookie->count());
    }
}

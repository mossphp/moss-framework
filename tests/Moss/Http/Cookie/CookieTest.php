<?php
namespace Moss\Http\Cookie;

function setcookie() {};

/**
 * @package Moss Test
 */
class CookieTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSet()
    {
        $cookie = new Cookie();
        $cookie->set('foo', 'bar');
        $this->assertEquals('bar', $cookie->get('foo'));
    }

    public function testSetWithEmptyKey()
    {
        $cookie = new Cookie();
        $cookie->set(null, 'yada');
        $this->assertEquals('yada', $cookie->get(0));
    }

    public function testRemove()
    {
        $cookie = new Cookie();
        $cookie->set('foo', 'bar');
        $this->assertEquals('bar', $cookie->get('foo'));
        $cookie->remove('foo');
        $this->assertNull($cookie->get('foo'));
    }

    public function testRemoveAll()
    {
        $cookie = new Cookie();
        $cookie->set('foo', 'bar');
        $this->assertEquals('bar', $cookie->get('foo'));
        $cookie->remove();
        $this->assertEquals(array(), $cookie->get());
    }

    public function testAll()
    {
        $cookie = new Cookie();
        $cookie->set('foo', 'bar');
        $cookie->set('yada', 'yada');
        $this->assertEquals(array('foo' => 'bar', 'yada' => 'yada'), $cookie->all());
    }

    public function testReset()
    {
        $cookie = new Cookie();
        $cookie->set('foo', 'bar');
        $cookie->set('yada', 'yada');
        $this->assertEquals(2, $cookie->count());
        $cookie->reset();
        $this->assertEquals(0, $cookie->count());
    }

    public function testOffsetUnset()
    {
        $cookie = new Cookie();
        $cookie['foo'] = 'bar';
        unset($cookie['foo']);
        $this->assertEquals(0, $cookie->count());
    }

    public function testOffsetSet()
    {
        $cookie = new Cookie();
        $cookie['foo'] = 'bar';
        $this->assertEquals('bar', $cookie->get('foo'));
    }


    public function testOffsetSetWithNullOffset()
    {
        $cookie = new Cookie();
        $cookie[] = 'bar';
        $this->assertEquals('bar', $cookie->get(0));
    }


    public function testOffsetGet()
    {
        $cookie = new Cookie();
        $cookie['foo'] = 'bar';
        $this->assertEquals('bar', $cookie['foo']);
    }

    public function testOffsetExists()
    {
        $cookie = new Cookie();
        $cookie['foo'] = 'bar';
        $this->assertTrue(isset($cookie['foo']));
    }

    public function testCurrent()
    {
        $cookie = new Cookie();
        $cookie['foo'] = 'bar';
        $this->assertEquals('bar', $cookie->current());
    }

    public function testNext()
    {
        $cookie = new Cookie();
        $cookie['foo'] = 'bar';
        $cookie->next();
        $this->assertFalse($cookie->current());
    }

    public function testKey()
    {
        $cookie = new Cookie();
        $cookie['foo'] = 'bar';
        $this->assertEquals('foo', $cookie->key());
    }

    public function testValid()
    {
        $cookie = new Cookie();
        $cookie['foo'] = 'bar';
        $cookie->rewind();
        $this->assertTrue($cookie->valid());
    }

    public function testRewind()
    {
        $cookie = new Cookie();
        $cookie['foo'] = 'bar';
        $cookie->rewind();
        $this->assertEquals('bar', $cookie->current());
    }

    public function testCount()
    {
        $cookie = new Cookie();
        $cookie['foo'] = 'bar';
        $this->assertEquals(1, $cookie->count());
    }
}

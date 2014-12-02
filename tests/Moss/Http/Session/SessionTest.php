<?php
namespace Moss\Http\Session;

function headers_sent(&$file = null, &$line = null) { return false; }
function session_start() { return true; }
function session_regenerate_id() { session_id('newRandomSID'); return true; }

/**
 * @package Moss Test
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{
    public function testRegenerate()
    {
        $session = new Session();
        $session->set('foo', 'bar');
        $id = $session->identify();
        $session->regenerate();
        $this->assertNotEquals($id, $session->identify());
        $this->assertEquals('bar', $session->get('foo'));
    }

    public function testIdentify()
    {
        $session = new Session();
        $this->assertEquals('someSessionIdentifier', $session->identify('someSessionIdentifier'));
    }

    public function testName()
    {
        $session = new Session();
        $this->assertEquals('someSessionName', $session->name('someSessionName'));
    }

    public function testGetSet()
    {
        $session = new Session();
        $session->set('foo', 'bar');
        $this->assertEquals('bar', $session->get('foo'));
    }

    public function testRemove()
    {
        $session = new Session();
        $session->set('foo', 'bar');
        $this->assertEquals('bar', $session->get('foo'));
        $session->remove('foo');
        $this->assertNull($session->get('foo'));
    }

    public function testAll()
    {
        $session = new Session();
        $session->set('foo', 'bar');
        $session->set('yada', 'yada');
        $this->assertEquals(['foo' => 'bar', 'yada' => 'yada'], $session->all());
    }

    public function testReset()
    {
        $session = new Session();
        $session->set('foo', 'bar');
        $session->set('yada', 'yada');
        $this->assertEquals(2, $session->count());
        $session->reset();
        $this->assertEquals(0, $session->count());
    }

    public function testOffsetUnset()
    {
        $session = new Session();
        $session['foo'] = 'bar';
        unset($session['foo']);
        $this->assertEquals(0, $session->count());
    }

    public function testOffsetSet()
    {
        $session = new Session();
        $session['foo'] = 'bar';
        $this->assertEquals('bar', $session['foo']);
    }


    public function testOffsetGet()
    {
        $session = new Session();
        $session['foo'] = 'bar';
        $this->assertEquals('bar', $session['foo']);
    }

    public function testOffsetExists()
    {
        $session = new Session();
        $session['foo'] = 'bar';
        $this->assertTrue(isset($session['foo']));
    }

    public function testCurrent()
    {
        $session = new Session();
        $session['foo'] = 'bar';
        $this->assertEquals('bar', $session->current());
    }


    public function testNext()
    {
        $session = new Session();
        $session['foo'] = 'bar';
        $session->next();
        $this->assertFalse($session->current());
    }

    public function testKey()
    {
        $session = new Session();
        $session['foo'] = 'bar';
        $this->assertEquals('foo', $session->key());
    }


    public function testValid()
    {
        $session = new Session();
        $session['foo'] = 'bar';
        $session->rewind();
        $this->assertTrue($session->valid());
    }

    public function testRewind()
    {
        $session = new Session();
        $session['foo'] = 'bar';
        $session->rewind();
        $this->assertEquals('bar', $session->current());
    }

    public function testCount()
    {
        $session = new Session();
        $session['foo'] = 'bar';
        $this->assertEquals(1, $session->count());
    }
}

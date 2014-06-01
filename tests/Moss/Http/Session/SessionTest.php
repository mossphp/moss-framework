<?php
namespace Moss\Http\Session;

class MockSession extends Session
{
    private $randCounter = 0;

    public function __construct($name = 'PHPSESSID', $cacheLimiter = '')
    {
        $this->name($name);
        $this->cacheLimiter($cacheLimiter);

        if (!$this->identify()) {
            $this->startSession();
        }

        $this->storage = array();
    }

    protected function startSession()
    {
        $this->storage = array();
        session_id($this->rand());
    }

    public function destroy()
    {
        $this->storage = array();

        return $this;
    }

    public function regenerate()
    {
        session_id($this->rand());

        return $this;
    }

    private function rand()
    {
        return md5($this->randCounter++);
    }
}

/**
 * @package Moss Test
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{
    public function testRegenerate()
    {
        $session = new MockSession();
        $session->set('foo', 'bar');
        $id = $session->identify();
        $session->regenerate();
        $this->assertNotEquals($id, $session->identify());
        $this->assertEquals('bar', $session->get('foo'));
    }

    public function testIdentify()
    {
        $session = new MockSession();
        $this->assertEquals('someSessionIdentifier', $session->identify('someSessionIdentifier'));
    }

    public function testName()
    {
        $session = new MockSession();
        $this->assertEquals('someSessionName', $session->name('someSessionName'));
    }

    public function testGetSet()
    {
        $session = new MockSession();
        $session->set('foo', 'bar');
        $this->assertEquals('bar', $session->get('foo'));
    }

    public function testRemove()
    {
        $session = new MockSession();
        $session->set('foo', 'bar');
        $this->assertEquals('bar', $session->get('foo'));
        $session->remove('foo');
        $this->assertNull($session->get('foo'));
    }

    public function testAll()
    {
        $session = new MockSession();
        $session->set('foo', 'bar');
        $session->set('yada', 'yada');
        $this->assertEquals(array('foo' => 'bar', 'yada' => 'yada'), $session->all());
    }

    public function testReset()
    {
        $session = new MockSession();
        $session->set('foo', 'bar');
        $session->set('yada', 'yada');
        $this->assertEquals(2, $session->count());
        $session->reset();
        $this->assertEquals(0, $session->count());
    }

    public function testOffsetUnset()
    {
        $session = new MockSession();
        $session['foo'] = 'bar';
        unset($session['foo']);
        $this->assertEquals(0, $session->count());
    }

    public function testOffsetSet()
    {
        $session = new MockSession();
        $session['foo'] = 'bar';
        $this->assertEquals('bar', $session['foo']);
    }


    public function testOffsetGet()
    {
        $session = new MockSession();
        $session['foo'] = 'bar';
        $this->assertEquals('bar', $session['foo']);
    }

    public function testOffsetExists()
    {
        $session = new MockSession();
        $session['foo'] = 'bar';
        $this->assertTrue(isset($session['foo']));
    }

    public function testCurrent()
    {
        $session = new MockSession();
        $session['foo'] = 'bar';
        $this->assertEquals('bar', $session->current());
    }


    public function testNext()
    {
        $session = new MockSession();
        $session['foo'] = 'bar';
        $session->next();
        $this->assertFalse($session->current());
    }

    public function testKey()
    {
        $session = new MockSession();
        $session['foo'] = 'bar';
        $this->assertEquals('foo', $session->key());
    }


    public function testValid()
    {
        $session = new MockSession();
        $session['foo'] = 'bar';
        $session->rewind();
        $this->assertTrue($session->valid());
    }

    public function testRewind()
    {
        $session = new MockSession();
        $session['foo'] = 'bar';
        $session->rewind();
        $this->assertEquals('bar', $session->current());
    }

    public function testCount()
    {
        $session = new MockSession();
        $session['foo'] = 'bar';
        $this->assertEquals(1, $session->count());
    }
}

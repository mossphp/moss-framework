<?php
namespace moss\http\session;

/**
 * @package Moss Test
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testRegenerate()
    {
        $session = new Session();
        $session->set('foo', 'bar');
        $id = $session->identify();
        $session->regenerate();
        $this->assertNotEquals($id, $session->identify());
        $this->assertEquals('bar', $session->get('foo'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testIdentify()
    {
        $session = new Session();
        $this->assertEquals('someSessionIdentifier', $session->identify('someSessionIdentifier'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testName()
    {
        $session = new Session();
        $this->assertEquals('someSessionName', $session->name('someSessionName'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetSet()
    {
        $session = new Session();
        $session->set('foo', 'bar');
        $this->assertEquals($_SESSION['foo'], $session->get('foo'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRemove()
    {
        $session = new Session();
        $session->set('foo', 'bar');
        $this->assertEquals($_SESSION['foo'], $session->get('foo'));
        $session->remove('foo');
        $this->assertArrayNotHasKey('foo', $_SESSION);
        $this->assertNull($session->get('foo'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testAll()
    {
        $session = new Session();
        $session->set('foo', 'bar');
        $session->set('yada', 'yada');
        $this->assertEquals(array('foo' => 'bar', 'yada' => 'yada'), $session->all());
    }

    /**
     * @runInSeparateProcess
     */
    public function testReset()
    {
        $session = new Session();
        $session->set('foo', 'bar');
        $session->set('yada', 'yada');
        $this->assertEquals(2, $session->count());
        $session->reset();
        $this->assertEquals(0, $session->count());
    }

    /**
     * @runInSeparateProcess
     */
    public function testOffsetUnset()
    {
        $session = new Session();
        $session->offsetSet('foo', 'bar');
        $session->offsetUnset('foo');
        $this->assertEquals(0, $session->count());
    }

    /**
     * @runInSeparateProcess
     */
    public function testOffsetSet()
    {
        $session = new Session();
        $session->offsetSet('foo', 'bar');
        $this->assertEquals('bar', $session['foo']);
        $this->assertEquals('bar', $_SESSION['foo']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testOffsetGet()
    {
        $session = new Session();
        $session->offsetSet('foo', 'bar');
        $this->assertEquals('bar', $session['foo']);
        $this->assertEquals('bar', $_SESSION['foo']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testOffsetExists()
    {
        $session = new Session();
        $session->offsetSet('foo', 'bar');
        $this->assertTrue(isset($session['foo']));
        $this->assertTrue(isset($_SESSION['foo']));
    }

    /**
     * @runInSeparateProcess
     */
    public function testCurrent()
    {
        $session = new Session();
        $session->offsetSet('foo', 'bar');
        $this->assertEquals(current($_SESSION), $session->current());
    }

    /**
     * @runInSeparateProcess
     */
    public function testNext()
    {
        $session = new Session();
        $session->offsetSet('foo', 'bar');
        $prev = current($_SESSION);
        $session->next();
        $this->assertEquals(current($_SESSION), $session->current());
        $this->assertNotEquals($prev, current($_SESSION));
    }

    /**
     * @runInSeparateProcess
     */
    public function testKey()
    {
        $session = new Session();
        $session->offsetSet('foo', 'bar');
        $this->assertEquals(key($_SESSION), $session->key());
    }

    /**
     * @runInSeparateProcess
     */
    public function testValid()
    {
        $session = new Session();
        $session->offsetSet('foo', 'bar');
        $session->rewind();
        $this->assertTrue($session->valid());
    }

    /**
     * @runInSeparateProcess
     */
    public function testRewind()
    {
        $session = new Session();
        $session->offsetSet('foo', 'bar');
        $session->rewind();
        $this->assertEquals(reset($_SESSION), $session->current());
    }

    /**
     * @runInSeparateProcess
     */
    public function testCount()
    {
        $session = new Session();
        $session->offsetSet('foo', 'bar');
        $this->assertEquals(count($_SESSION), $session->count());
    }
}

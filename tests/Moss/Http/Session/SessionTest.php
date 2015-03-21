<?php
namespace Moss\Http\Session;

class FunctionMockSession
{
    public static $ini;
    public static $headersSent;

    public static $sessionStatus;
    public static $sessionStart;
    public static $sessionId;
}

function ini_get($varname) { return isset(FunctionMockSession::$ini[$varname]) ? FunctionMockSession::$ini[$varname] : null; }

function headers_sent(&$file = null, &$line = null) { return FunctionMockSession::$headersSent; }

function session_status() { return FunctionMockSession::$sessionStatus; }

function session_start() { return FunctionMockSession::$sessionStart; }

function session_id($id = null)
{
    if ($id) {
        FunctionMockSession::$sessionId = $id;
    }

    return FunctionMockSession::$sessionId;
}

;
function session_regenerate_id()
{
    session_id('newRandomSID');

    return true;
}

function session_destroy() {}

/**
 * @package Moss Test
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        FunctionMockSession::$headersSent = false;
        FunctionMockSession::$sessionStatus = \PHP_SESSION_NONE;
        FunctionMockSession::$sessionStart = true;
        FunctionMockSession::$sessionId = 'SessionId';
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Session already started by PHP
     */
    public function testSessionAlreadyStarted()
    {
        FunctionMockSession::$sessionId = null;
        FunctionMockSession::$sessionStatus = \PHP_SESSION_ACTIVE;

        new Session();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to start session, headers have already been sent by
     */
    public function testHeadersSent()
    {
        FunctionMockSession::$sessionId = null;
        FunctionMockSession::$ini['session.use_cookies'] = true;
        FunctionMockSession::$headersSent = true;

        new Session();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to start session
     */
    public function testUnableToStartSession()
    {
        FunctionMockSession::$sessionId = null;
        FunctionMockSession::$sessionStart = false;

        new Session();
    }

    public function testDestroy()
    {
        $_SESSION = ['foo', 'bar'];

        $session = new Session();
        $session->destroy();

        $this->assertEquals([], $_SESSION);
    }

    public function testRegenerate()
    {
        $session = new Session();
        $session->set('foo', 'bar');
        $id = $session->identify();
        $session->regenerate();
        $this->assertNotEquals($id, $session->identify());
        $this->assertEquals('bar', $session->get('foo'));
    }

    public function testRegenerateWithoutSESSION()
    {
        $session = new Session();
        $id = $session->identify();

        unset($_SESSION);

        $session->regenerate();
        $this->assertNotEquals($id, $session->identify());
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

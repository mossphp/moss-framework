<?php
namespace Moss\Http\session;

/**
 * @package Moss Test
 */
class FlashBagTest extends \PHPUnit_Framework_TestCase
{
    protected $session;

    public function testCount()
    {
        $bag = new FlashBag($this->sessionMock());
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');
        $this->assertEquals(2, $bag->count());
    }

    public function testReset()
    {
        $bag = new FlashBag($this->sessionMock());
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');
        $bag->reset();

        $this->assertEquals(0, $bag->count());
    }

    public function testHasAny()
    {
        $bag = new FlashBag($this->sessionMock());

        $this->assertFalse($bag->has());

        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');

        $this->assertTrue($bag->has());
    }

    public function testHasType()
    {
        $bag = new FlashBag($this->sessionMock());
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');

        $this->assertTrue($bag->has('bar'));
        $this->assertFalse($bag->has('boing'));
    }

    public function testGetAll()
    {
        $bag = new FlashBag($this->sessionMock());
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');

        $result = array(
            array('message' => 'foo', 'type' => 'bar'),
            array('message' => 'yada', 'type' => 'yada'),
        );

        $this->assertEquals($result, $bag->get());
    }

    public function testGetType()
    {
        $bag = new FlashBag($this->sessionMock());
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');

        $result = array(
            array('message' => 'foo', 'type' => 'bar'),
        );

        $this->assertEquals($result, $bag->get('bar'));
    }

    public function testRetrieve()
    {
        $bag = new FlashBag($this->sessionMock());
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');

        $result = array(
            array('message' => 'foo', 'type' => 'bar'),
            array('message' => 'yada', 'type' => 'yada'),
        );

        $this->assertEquals($result[0], $bag->retrieve());
        $this->assertEquals($result[1], $bag->retrieve());
        $this->assertEquals(0, $bag->count());
    }

    public function testOffsetUnset()
    {
        $bag = new FlashBag($this->sessionMock());
        $bag->add('foo', 'bar');
        unset($bag[0]);
        $this->assertFalse(false, $bag->retrieve());
    }

    public function testOffsetGetSet()
    {
        $msg = array('message' => 'foo', 'type' => 'bar');

        $bag = new FlashBag($this->sessionMock());
        $bag[0] = $msg;
        $this->assertEquals($msg, $bag[0]);
        $this->assertFalse(false, $bag->retrieve());
    }

    public function testOffsetGetEmpty()
    {
        $bag = new FlashBag($this->sessionMock());
        $this->assertNull($bag[0]);
    }

    public function testOffsetSetWithoutKey()
    {
        $msg = array('message' => 'foo', 'type' => 'bar');

        $bag = new FlashBag($this->sessionMock());
        $bag[] = $msg;
        $this->assertEquals($msg, $bag[0]);
        $this->assertFalse(false, $bag->retrieve());
    }

    public function testOffsetExists()
    {
        $bag = new FlashBag($this->sessionMock());
        $bag->add('foo', 'bar');
        $this->assertTrue(isset($bag[0]));
        $this->assertEquals(1, $bag->count());
    }

    public function testIterator()
    {
        $bag = new FlashBag($this->sessionMock());
        $bag->add('foo0', 'bar0');
        $bag->add('foo1', 'bar1');
        $bag->add('foo2', 'bar2');
        $bag->add('foo3', 'bar3');
        $bag->add('foo4', 'bar4');

        $i = 0;
        foreach ($bag as $val) {
            $this->assertEquals(array('message' => 'foo'.$i, 'type' => 'bar'.$i), $val);
            $i++;
        }
    }

    protected function sessionMock()
    {
        $session = & $this->session;

        $mock = $this->getMock('\Moss\Http\session\SessionInterface');
        $mock
            ->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnCallback(array($this, 'sessionMockGet')));

        $mock = $this->getMock('\Moss\Http\session\SessionInterface');
        $mock
            ->expects($this->any())
            ->method('offsetSet')
            ->will($this->returnCallback(array($this, 'sessionMockSet')));

        return $session;
    }

    protected function & sessionMockGet($offset = null)
    {
        if ($offset === null) {
            return $this->session;
        }

        return $this->session[$offset];
    }

    protected function & sessionMockSet($offset, $value)
    {
        return $this->session[$offset] = $value;
    }

}

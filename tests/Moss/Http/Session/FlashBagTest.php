<?php
namespace Moss\Http\Session;

use Moss\Bag\Bag;

class MockSessionInterface extends Bag implements SessionInterface
{
    public function regenerate() { }

    public function destroy() { }

    public function identify($identifier = null) { }

    public function name($name = null) { }
}

/**
 * @package Moss Test
 */
class FlashBagTest extends \PHPUnit_Framework_TestCase
{
    protected $session = [];

    public function testCount()
    {
        $bag = new FlashBag(new MockSessionInterface($this->session));
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');
        $this->assertEquals(2, $bag->count());
    }

    public function testReset()
    {
        $bag = new FlashBag(new MockSessionInterface($this->session));
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');
        $bag->reset();

        $this->assertEquals(0, $bag->count());
    }

    public function testHasAny()
    {
        $bag = new FlashBag(new MockSessionInterface($this->session));

        $this->assertFalse($bag->has());

        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');

        $this->assertTrue($bag->has());
    }

    public function testHasType()
    {
        $bag = new FlashBag(new MockSessionInterface($this->session));
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');

        $this->assertTrue($bag->has('bar'));
        $this->assertFalse($bag->has('boing'));
    }

    public function testGetAll()
    {
        $bag = new FlashBag(new MockSessionInterface($this->session));
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');

        $result = [
            ['message' => 'foo', 'type' => 'bar'],
            ['message' => 'yada', 'type' => 'yada'],
        ];

        $this->assertEquals($result, $bag->get());
    }

    public function testGetType()
    {
        $bag = new FlashBag(new MockSessionInterface($this->session));
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');

        $result = [
            ['message' => 'foo', 'type' => 'bar'],
        ];

        $this->assertEquals($result, $bag->get('bar'));
    }

    public function testRetrieve()
    {
        $bag = new FlashBag(new MockSessionInterface($this->session));
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');

        $result = [
            ['message' => 'foo', 'type' => 'bar'],
            ['message' => 'yada', 'type' => 'yada'],
        ];

        $this->assertEquals($result[0], $bag->retrieve());
        $this->assertEquals($result[1], $bag->retrieve());
        $this->assertEquals(0, $bag->count());
    }

    public function testOffsetUnset()
    {
        $bag = new FlashBag(new MockSessionInterface($this->session));
        $bag->add('foo', 'bar');
        unset($bag[0]);
        $this->assertFalse(false, $bag->retrieve());
    }

    public function testOffsetGetSet()
    {
        $msg = ['message' => 'foo', 'type' => 'bar'];

        $bag = new FlashBag(new MockSessionInterface($this->session));
        $bag[0] = $msg;
        $this->assertEquals($msg, $bag[0]);
        $this->assertFalse(false, $bag->retrieve());
    }

    public function testOffsetGetEmpty()
    {
        $bag = new FlashBag(new MockSessionInterface($this->session));
        $this->assertNull($bag[0]);
    }

    public function testOffsetSetWithoutKey()
    {
        $msg = ['message' => 'foo', 'type' => 'bar'];

        $bag = new FlashBag(new MockSessionInterface($this->session));
        $bag[] = $msg;
        $this->assertEquals($msg, $bag[0]);
        $this->assertFalse(false, $bag->retrieve());
    }

    public function testOffsetExists()
    {
        $bag = new FlashBag(new MockSessionInterface($this->session));
        $bag->add('foo', 'bar');
        $this->assertTrue(isset($bag[0]));
        $this->assertEquals(1, $bag->count());
    }

    public function testIterator()
    {
        $bag = new FlashBag(new MockSessionInterface($this->session));
        $bag->add('foo0', 'bar0');
        $bag->add('foo1', 'bar1');
        $bag->add('foo2', 'bar2');
        $bag->add('foo3', 'bar3');
        $bag->add('foo4', 'bar4');

        $i = 0;
        foreach ($bag as $key => $val) {
            $this->assertEmpty(0, $key);
            $this->assertEquals(['message' => 'foo' . $i, 'type' => 'bar' . $i], $val);
            $i++;
        }

        $this->assertEmpty($bag->get());
    }
}

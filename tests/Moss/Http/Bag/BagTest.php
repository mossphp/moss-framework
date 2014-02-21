<?php
namespace Moss\Http\bag;


class BagTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider dataProvider
     */
    public function testGetSet($offset, $value)
    {
        $bag = new Bag();
        $bag->set($offset, $value);
        $this->assertEquals($value, $bag->get($offset));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetAll($offset, $value, $expected)
    {
        $bag = new Bag();
        $bag->set($offset, $value);
        $this->assertEquals($expected, $bag->get());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHasWithoutParam($offset, $value)
    {
        $bag = new Bag();
        $bag->set($offset, $value);
        $this->assertTrue($bag->has());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHas($offset, $value)
    {
        $bag = new Bag();
        $bag->set($offset, $value);
        $this->assertTrue($bag->has($offset));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAll($offset, $value, $expected)
    {
        $bag = new Bag();
        $bag->set($offset, $value);
        $this->assertEquals($expected, $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAllReplace($offset, $value, $expected)
    {
        $bag = new Bag();
        $bag->all(array($offset => $value));
        $this->assertEquals($expected, $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testRemove($offset, $value, $expected, $removed = array())
    {
        $bag = new Bag();
        $bag->set($offset, $value);
        $this->assertEquals($expected, $bag->all());
        $bag->remove($offset);
        $this->assertEquals($removed, $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testRemoveAll($offset, $value, $expected)
    {
        $bag = new Bag();
        $bag->set($offset, $value);
        $this->assertEquals($expected, $bag->all());
        $bag->remove();
        $this->assertEquals(array(), $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testReset($offset, $value, $expected)
    {
        $bag = new Bag();
        $bag->set($offset, $value);
        $this->assertEquals($expected, $bag->all());
        $bag->reset($offset);
        $this->assertEquals(array(), $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetUnset($offset, $value)
    {
        $bag = new Bag();
        $bag->offsetSet($offset, $value);
        $bag->offsetUnset($offset);
        $this->assertEquals(0, $bag->count());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetGetSet($offset, $value)
    {
        $bag = new Bag();
        $bag->offsetSet($offset, $value);
        $this->assertEquals($value, $bag[$offset]);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetGetEmpty($offset)
    {
        $bag = new Bag();
        $this->assertNull(null, $bag[$offset]);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetSetWithoutKey($value)
    {
        $bag = new Bag();
        $bag[] = $value;
        $this->assertEquals($value, $bag[0]);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetExists($offset, $value)
    {
        $bag = new Bag();
        $bag->offsetSet($offset, $value);
        $this->assertTrue(isset($bag[$offset]));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testIterator($offset, $value)
    {
        $bag = new Bag();
        $bag->offsetSet($offset, $value);

        foreach ($bag as $key => $val) {
            $this->assertEquals($key, $offset);
            $this->assertEquals($val, $value);
        }
    }

    /**
     * @dataProvider dataProvider
     */
    public function testCount($offset, $value)
    {
        $bag = new Bag();
        $bag->offsetSet(1, $offset);
        $bag->offsetSet(2, $value);
        $this->assertEquals(2, $bag->count());
    }

    public function dataProvider()
    {
        return array(
            array('foo', 1, array('foo' => 1)),
            array('bar', 'lorem', array('bar' => 'lorem')),
            array('yada', array('yada' => 'yada'), array('yada' => array('yada' => 'yada'))),
            array('dada', new \stdClass(), array('dada' => new \stdClass())),
            array('foo.bar', 'yada', array('foo' => array('bar' => 'yada')), array('foo' => array()))
        );
    }
}
 
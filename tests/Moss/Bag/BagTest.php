<?php
namespace Moss\Bag;


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

    public function testGetWithDefaultValue()
    {
        $bag = new Bag();
        $this->assertEquals('bar', $bag->get('foo', 'bar'));
    }

    public function testSetArray()
    {
        $bag = new Bag();
        $bag->set(['foo' => 'bar']);
        $this->assertEquals('bar', $bag->get('foo'));
    }

    public function testAddElementToStringValue()
    {
        $bag = new Bag();
        $bag->set('foo', 'foo');
        $bag->set('foo.bar', 'bar');
        $this->assertEquals([0 => 'foo', 'bar' => 'bar'], $bag->get('foo'));
    }

    public function testSetWithoutOffset()
    {
        $bag = new Bag();
        $bag->set('foo', 'foo');
        $bag->set(null, 'bar');
        $this->assertEquals('bar', $bag->get(0));
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
        $bag->all([$offset => $value]);
        $this->assertEquals($expected, $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testRemove($offset, $value, $expected, $removed = [])
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
        $this->assertEquals([], $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testReset($offset, $value, $expected)
    {
        $bag = new Bag();
        $bag->set($offset, $value);
        $this->assertEquals($expected, $bag->all());
        $bag->reset();
        $this->assertEquals([], $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetUnset($offset, $value)
    {
        $bag = new Bag();
        $bag[$offset] = $value;
        unset($bag[$offset]);
        $this->assertEquals(0, $bag->count());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetGetSet($offset, $value)
    {
        $bag = new Bag();
        $bag[$offset] = $value;
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
        $bag[$offset] = $value;
        $this->assertTrue(isset($bag[$offset]));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testIterator($offset, $value)
    {
        $bag = new Bag();
        $bag[$offset] = $value;

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
        $bag[1] = $offset;
        $bag[2] = $value;
        $this->assertEquals(2, $bag->count());
    }

    public function dataProvider()
    {
        return [
            ['foo', 1, ['foo' => 1]],
            ['bar', 'lorem', ['bar' => 'lorem']],
            ['yada', ['yada' => 'yada'], ['yada' => ['yada' => 'yada']]],
            ['dada', new \stdClass(), ['dada' => new \stdClass()]],
            ['foo.bar', 'yada', ['foo' => ['bar' => 'yada']], ['foo' => []]]
        ];
    }
}

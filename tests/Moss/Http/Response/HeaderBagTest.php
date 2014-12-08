<?php
namespace Moss\Http\Response;


class HeaderBagTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider dataProvider
     */
    public function testGetSet($offset, $value)
    {
        $bag = new HeaderBag();
        $bag->set($offset, $value);
        $this->assertEquals($value, $bag->get($offset));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetAll($offset, $value)
    {
        $bag = new HeaderBag();
        $bag->set($offset, $value);
        $this->assertEquals([$offset => $value], $bag->get());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHas($offset, $value)
    {
        $bag = new HeaderBag();
        $bag->set($offset, $value);
        $this->assertTrue($bag->has());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHasOffset($offset, $value)
    {
        $bag = new HeaderBag();
        $bag->set($offset, $value);
        $this->assertTrue($bag->has($offset));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAll($offset, $value)
    {
        $bag = new HeaderBag();
        $bag->set($offset, $value);
        $this->assertEquals([$offset => $value], $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAllReplace($offset, $value)
    {
        $bag = new HeaderBag();
        $bag->all([$offset => $value]);
        $this->assertEquals([$offset => $value], $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testRemove($offset, $value)
    {
        $bag = new HeaderBag();
        $bag->set($offset, $value);
        $this->assertEquals([$offset => $value], $bag->all());
        $bag->remove($offset);
        $this->assertEquals([], $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testRemoveAll($offset, $value)
    {
        $bag = new HeaderBag();
        $bag->set($offset, $value);
        $this->assertEquals([$offset => $value], $bag->all());
        $bag->remove();
        $this->assertEquals([], $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testReset($offset, $value)
    {
        $bag = new HeaderBag();
        $bag->set($offset, $value);
        $this->assertEquals([$offset => $value], $bag->all());
        $bag->reset($offset);
        $this->assertEquals([], $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetUnset($offset, $value)
    {
        $bag = new HeaderBag();
        $bag[$offset] = $value;
        unset($bag[$offset]);
        $this->assertEquals(0, $bag->count());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetGetSet($offset, $value)
    {
        $bag = new HeaderBag();
        $bag[$offset] = $value;
        $this->assertEquals($value, $bag[$offset]);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetGetEmpty($offset)
    {
        $bag = new HeaderBag();
        $this->assertNull(null, $bag[$offset]);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetSetWithoutKey($value)
    {
        $bag = new HeaderBag();
        $bag[] = $value;
        $this->assertEquals($value, $bag[0]);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetExists($offset, $value)
    {
        $bag = new HeaderBag();
        $bag[$offset] = $value;
        $this->assertTrue(isset($bag[$offset]));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testIterator($offset, $value)
    {
        $bag = new HeaderBag();
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
        $bag = new HeaderBag();
        $bag[1] = $offset;
        $bag[2] = $value;
        $this->assertEquals(2, $bag->count());
    }

    public function testAsArray()
    {
        $headers = [
            'Content-Type'=> 'text/html; charset=UTF-8',
            'Location'=> 'http://google.com',
            'Refresh'=> '10; URL=http://google.com',
        ];

        $expected = [
            'Content-Type: text/html; charset=UTF-8',
            'Location: http://google.com',
            'Refresh: 10; URL=http://google.com',
        ];

        $bag = new HeaderBag($headers);
        $this->assertEquals($expected, $bag->asArray());
    }

    public function dataProvider()
    {
        return [
            ['Content-Type', 'text/plain'],
            ['Content-Type', 'text/html; charset=UTF-8'],
            ['Location', 'http://google.com'],
            ['Refresh', '10; URL=http://google.com'],
        ];
    }
}

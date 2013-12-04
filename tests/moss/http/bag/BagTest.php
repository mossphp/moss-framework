<?php
namespace moss\http\bag;


class BagTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider dataProvider
     */
    public function testGetSet($offset, $value, $get)
    {
        $bag = new Bag();
        $bag->set($offset, $value);
        $this->assertEquals($value, $bag->get($get));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHas($offset, $value)
    {
        $bag = new Bag();
        $bag->set($offset, $value);
        $this->assertTrue($bag->has($offset));
        $this->assertFalse($bag->has($value));
    }

    /**
     * @dataProvider dataProvider
     */
    public function dataProvider()
    {
        return array(
            array('foo', null, 'foo'),
            array('foo', 'bar', 'foo'),
            array('foo.bar', 'yada', 'foo.bar'),
            array('f.o.o.b.a.r', 'yada', 'f.o.o.b.a.r'),
        );
    }

    /**
     * @dataProvider allProvider
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
    public function allProvider()
    {
        return array(
            array('foo', 'bar', array('foo' => 'bar')),
            array('foo.bar', 'yada', array('foo' => array('bar' => 'yada'))),
        );
    }

    /**
     * @dataProvider removeProvider
     */
    public function testRemove($offset, $value, $remove, $expected)
    {
        $bag = new Bag();
        $bag->set($offset, $value);
        $this->assertEquals($value, $bag->get($offset));
        $bag->remove($remove);
        $this->assertEquals($expected, $bag->all());
    }

    public function removeProvider()
    {
        return array(
            array('foo', 'bar', 'foo', array()),
            array('foo.bar', 'bar', 'foo', array()),
            array('foo.bar', 'bar', 'foo.bar', array('foo' => array())),
        );
    }

    /**
     * @dataProvider resetProvider
     */
    public function testReset($offset, $value)
    {
        $bag = new Bag();
        $bag->set($offset, $value);
        $this->assertEquals($value, $bag->get($offset));
        $bag->reset();
        $this->assertEquals(array(), $bag->all());
    }

    public function resetProvider()
    {
        return array(
            array('foo', 'bar'),
            array('foo.bar', 'yada'),
            array('f.o.o.b.a.r', 'yada'),
        );
    }
}
 
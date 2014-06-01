<?php
namespace Moss\View;

class MockView extends View
{
    public function render()
    {
        return json_encode(array(parent::translate($this->template), $this->storage));
    }
}

class ViewTest extends \PHPUnit_Framework_TestCase
{

    public function testTemplate()
    {
        $view = new MockView();
        $view->template('foo:bar:yada');
        $this->assertEquals('["..\/src\/foo\/bar\/View\/yada.php",[]]', $view->render());
    }

    /**
     * @dataProvider setProvider
     */
    public function testSet($result, $key, $value = null)
    {
        $view = new MockView();
        $view
            ->template('foo:bar:yada')
            ->set($key, $value);

        $this->assertEquals($result, $view->render());
    }

    public function setProvider()
    {
        return array(
            array('["..\/src\/foo\/bar\/View\/yada.php",{"a":null}]', 'a'),
            array('["..\/src\/foo\/bar\/View\/yada.php",{"b":"c"}]', 'b', 'c'),
            array('["..\/src\/foo\/bar\/View\/yada.php",["d","e"]]', array('d', 'e')),
            array('["..\/src\/foo\/bar\/View\/yada.php",{"f":["g","h"]}]', 'f', array('g', 'h')),
            array('["..\/src\/foo\/bar\/View\/yada.php",{"i":"j","k":"l"}]', array('i' => 'j', 'k' => 'l')),
            array('["..\/src\/foo\/bar\/View\/yada.php",{"m":{"n":"o"}}]', 'm', array('n' => 'o')),
            array('["..\/src\/foo\/bar\/View\/yada.php",{"m":{"n":"o"}}]', 'm.n', 'o')
        );
    }

    /**
     * @dataProvider getProvider
     */
    public function testGet($result, $name, $key, $value = null)
    {
        $view = new MockView();
        $view
            ->template('foo:bar:yada')
            ->set($key, $value);

        $this->assertEquals($result, $view->get($name));
    }

    public function getProvider()
    {
        return array(
            array(null, 'a', 'a'),
            array('c', 'b', 'b', 'c'),
            array(0, 'd', array('d', 'e')),
            array(array('g', 'h'), 'f', 'f', array('g', 'h')),
            array('j', 'i', array('i' => 'j', 'k' => 'l')),
            array('o', 'm.n', 'm', array('n' => 'o')),
            array('o', 'm.n', array('m' => array('n' => 'o')))
        );
    }

    public function testRender()
    {
        $view = new MockView();
        $result = $view
            ->template('foo:bar:yada')
            ->render();

        $this->assertEquals('["..\/src\/foo\/bar\/View\/yada.php",[]]', $result);
    }

    public function testToString()
    {
        $view = new MockView();
        $result = $view
            ->template('foo:bar:yada')
            ->__toString();

        $this->assertEquals('["..\/src\/foo\/bar\/View\/yada.php",[]]', $result);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetUnset($offset, $value)
    {
        $view = new MockView();
        $view[$offset] = $value;
        unset($view[$offset]);
        $this->assertEquals(0, $view->count());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetGetSet($offset, $value)
    {
        $view = new MockView();
        $view[$offset] = $value;
        $this->assertEquals($value, $view[$offset]);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetGetEmpty($offset)
    {
        $view = new MockView();
        $this->assertNull(null, $view[$offset]);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetSetWithoutKey($value)
    {
        $view = new MockView();
        $view[] = $value;
        $this->assertEquals($value, $view[0]);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetExists($offset, $value)
    {
        $view = new MockView();
        $view[$offset] = $value;
        $this->assertTrue(isset($view[$offset]));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testCount($offset, $value)
    {
        $view = new MockView();
        $view[1] = $offset;
        $view[2] = $value;
        $this->assertEquals(2, $view->count());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testIterator($offset, $value)
    {
        $view = new MockView();
        $view[$offset] = $value;

        foreach ($view as $key => $val) {
            $this->assertEquals($key, $offset);
            $this->assertEquals($val, $value);
        }
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

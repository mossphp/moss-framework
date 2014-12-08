<?php
namespace Moss\View;

class MockView extends View
{
    public function render()
    {
        return json_encode([parent::translate($this->template), $this->storage]);
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
        return [
            ['["..\/src\/foo\/bar\/View\/yada.php",{"a":null}]', 'a'],
            ['["..\/src\/foo\/bar\/View\/yada.php",{"b":"c"}]', 'b', 'c'],
            ['["..\/src\/foo\/bar\/View\/yada.php",["d","e"]]', ['d', 'e']],
            ['["..\/src\/foo\/bar\/View\/yada.php",{"f":["g","h"]}]', 'f', ['g', 'h']],
            ['["..\/src\/foo\/bar\/View\/yada.php",{"i":"j","k":"l"}]', ['i' => 'j', 'k' => 'l']],
            ['["..\/src\/foo\/bar\/View\/yada.php",{"m":{"n":"o"}}]', 'm', ['n' => 'o']],
            ['["..\/src\/foo\/bar\/View\/yada.php",{"m":{"n":"o"}}]', 'm.n', 'o']
        ];
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
        return [
            [null, 'a', 'a'],
            ['c', 'b', 'b', 'c'],
            [0, 'd', ['d', 'e']],
            [['g', 'h'], 'f', 'f', ['g', 'h']],
            ['j', 'i', ['i' => 'j', 'k' => 'l']],
            ['o', 'm.n', 'm', ['n' => 'o']],
            ['o', 'm.n', ['m' => ['n' => 'o']]]
        ];
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
        return [
            ['foo', 1, ['foo' => 1]],
            ['bar', 'lorem', ['bar' => 'lorem']],
            ['yada', ['yada' => 'yada'], ['yada' => ['yada' => 'yada']]],
            ['dada', new \stdClass(), ['dada' => new \stdClass()]],
            ['foo.bar', 'yada', ['foo' => ['bar' => 'yada']], ['foo' => []]]
        ];
    }
}

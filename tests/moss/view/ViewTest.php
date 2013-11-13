<?php
namespace tests\moss\view;

use moss\view\View;

class ViewTest extends \PHPUnit_Framework_TestCase
{

    public function testTemplate()
    {
        $View = new View($this->getTwigMock());
        $View->template('foo');
        $this->assertEquals('["foo",[]]', $View->render());
    }

    /**
     * @dataProvider setProvider
     */
    public function testSet($result, $key, $value = null)
    {
        $View = new View($this->getTwigMock());
        $View
            ->template('foo')
            ->set($key, $value);

        $this->assertEquals($result, $View->render());
    }

    public function setProvider()
    {
        return array(
            array('["foo",{"a":null}]', 'a'),
            array('["foo",{"b":"c"}]', 'b', 'c'),
            array('["foo",["d","e"]]', array('d', 'e')),
            array('["foo",{"f":["g","h"]}]', 'f', array('g', 'h')),
            array('["foo",{"i":"j","k":"l"}]', array('i' => 'j', 'k' => 'l')),
            array('["foo",{"m":{"n":"o"}}]', 'm', array('n' => 'o')),
            array('["foo",{"m":{"n":"o"}}]', 'm.n', 'o')
        );
    }

    /**
     * @dataProvider getProvider
     */
    public function testGet($result, $name, $key, $value = null)
    {
        $View = new View($this->getTwigMock());
        $View
            ->template('foo')
            ->set($key, $value);

        $this->assertEquals($result, $View->get($name));
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
        $View = new View($this->getTwigMock());
        $result = $View
            ->template('foo')
            ->render();

        $this->assertEquals('["foo",[]]', $result);
    }

    public function testToString()
    {
        $View = new View($this->getTwigMock());
        $result = $View
            ->template('foo')
            ->__toString();

        $this->assertEquals('["foo",[]]', $result);
    }

    public function getTwigMock()
    {
        $Twig = $this->getMock('\Twig_Environment');
        $Twig
            ->expects($this->any())
            ->method('render')
            ->will(
                $this->returnCallback(
                    function () {
                        return json_encode(func_get_args());
                    }
                )
            );

        return $Twig;
    }
}

<?php
namespace Moss\Container;

class Foobar
{
    public $args;

    public function __construct()
    {
        $this->args = func_get_args();
    }

    public function foo()
    {
        $this->args = func_get_args();
    }
}

class ComponentTest extends \PHPUnit_Framework_TestCase
{

    public function testGetNoArgs()
    {
        $component = new Component('\Moss\Container\Foobar', array());

        $result = new Foobar;
        $this->assertEquals($result, $component->get());
    }

    public function testSimpleArgs()
    {
        $component = new Component('\Moss\Container\Foobar', array('foo', 'bar', array('y', 'a', 'd', 'a')));

        $result = new Foobar('foo', 'bar', array('y', 'a', 'd', 'a'));
        $this->assertEquals($result, $component->get());
    }

    /**
     * @expectedException \Moss\Container\ContainerException
     * @expectedExceptionMessage Unable to resolve dependency for
     */
    public function testComponentArgsWithoutContainer()
    {
        $component = new Component('\Moss\Container\Foobar', array('@foo', '@bar', '@yada'));
        $this->assertEquals(new Foobar, $component->get());
    }

    public function testComponentArgsWithContainer()
    {
        $container = $this->getMock('\Moss\Container\ContainerInterface');
        $container
            ->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue('foo'));

        $component = new Component('\Moss\Container\Foobar', array('@foo', '@bar', '@yada', '@Container'));

        $result = new Foobar('foo', 'foo', 'foo', $container);
        $this->assertEquals($result, $component->get($container));
    }

    public function testComponentMethods()
    {
        $component = new Component('\Moss\Container\Foobar', array(), array('foo' => array('foo', 'bar', 'yada')));
        $this->assertAttributeEquals(array('foo', 'bar', 'yada'), 'args', $component->get());
    }

    public function testCallable()
    {
        $container = $this->getMock('\Moss\Container\ContainerInterface');
        $container
            ->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue('foo'));

        $component = new Component('\Moss\Container\Foobar', array('@foo', '@bar', '@yada', '@Container'));

        $this->assertEquals($component($container), $component->get($container));
    }
}

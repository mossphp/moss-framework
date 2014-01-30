<?php
namespace moss\container;

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
        $component = new Component('\moss\container\Foobar', array());

        $result = new \moss\container\Foobar;
        $this->assertEquals($result, $component->get());
    }

    public function testSimpleArgs()
    {
        $component = new Component('\moss\container\Foobar', array('foo', 'bar', array('y', 'a', 'd', 'a')));

        $result = new \moss\container\Foobar('foo', 'bar', array('y', 'a', 'd', 'a'));
        $this->assertEquals($result, $component->get());
    }

    /**
     * @expectedException \moss\container\ContainerException
     * @expectedExceptionMessage Unable to resolve dependency for
     */
    public function testComponentArgsWithoutContainer()
    {
        $component = new Component('\moss\container\Foobar', array('@foo', '@bar', '@yada'));
        $this->assertEquals(new \moss\container\Foobar, $component->get());
    }

    public function testComponentArgsWithContainer()
    {
        $container = $this->getMock('\moss\container\ContainerInterface');
        $container
            ->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue('foo'));

        $component = new Component('\moss\container\Foobar', array('@foo', '@bar', '@yada', '@Container'));

        $result = new \moss\container\Foobar('foo', 'foo', 'foo', $container);
        $this->assertEquals($result, $component->get($container));
    }

    public function testComponentMethods()
    {
        $component = new Component('\moss\container\Foobar', array(), array('foo' => array('foo', 'bar', 'yada')));
        $this->assertAttributeEquals(array('foo', 'bar', 'yada'), 'args', $component->get());
    }

    public function testCallable()
    {
        $container = $this->getMock('\moss\container\ContainerInterface');
        $container
            ->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue('foo'));

        $component = new Component('\moss\container\Foobar', array('@foo', '@bar', '@yada', '@Container'));

        $this->assertEquals($component($container), $component->get($container));
    }
}

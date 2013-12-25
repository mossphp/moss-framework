<?php
namespace moss\container;


class ComponentTest extends \PHPUnit_Framework_TestCase
{

    public function testGetNoArgs()
    {
        $component = new Component('\tests\moss\Foobar', array());
        $this->assertEquals(new \tests\moss\Foobar, $component->get());
    }

    public function testSimpleArgs()
    {
        $component = new Component('\tests\moss\Foobar', array('foo', 'bar', array('y', 'a', 'd', 'a')));
        $this->assertEquals(new \tests\moss\Foobar('foo', 'bar', array('y', 'a', 'd', 'a')), $component->get());
    }

    /**
     * @expectedException \moss\container\ContainerException
     * @expectedExceptionMessage Foo
     */
    public function testComponentArgsWithoutContainer()
    {
        $component = new Component('\tests\moss\Foobar', array('@foo', '@bar', '@yada'));
        $this->assertEquals(new \tests\moss\Foobar, $component->get());
    }

    public function testComponentArgsWithContainer()
    {
        $container = $this->getMock('\moss\container\ContainerInterface');
        $container
            ->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue('foo'));

        $component = new Component('\tests\moss\Foobar', array('@foo', '@bar', '@yada', '@Container'));
        $this->assertEquals(new \tests\moss\Foobar('foo', 'foo', 'foo', $container), $component->get($container));
    }

    public function testComponentMethods()
    {
        $component = new Component('\tests\moss\Foobar', array(), array('foo' => array('foo', 'bar', 'yada')));
        $this->assertAttributeEquals(array('foo', 'bar', 'yada'), 'args', $component->get());
    }
}

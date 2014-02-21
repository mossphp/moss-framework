<?php
namespace Moss\Container;

class ContainerTest extends \PHPUnit_Framework_TestCase
{


    public function testValue()
    {
        $container = new Container();
        $container->register('foo', 'bar');
        $this->assertEquals('bar', $container->get('foo'));
    }

    /**
     * @expectedException \Moss\Container\ContainerException
     * @expectedExceptionMessage Invalid or unknown component/parameter identifier "foo.bar"
     */
    public function testInvalidValue()
    {
        $container = new Container();
        $container->register('foo', 'bar');
        $container->get('foo.bar');
    }

    public function testDeepValueFromArray()
    {
        $container = new Container();
        $container->register('foo', array('bar' => 'yada'));
        $this->assertEquals(array('bar' => 'yada'), $container->get('foo'));
        $this->assertEquals('yada', $container->get('foo.bar'));
    }

    public function testClosure()
    {
        $container = new Container();
        $container->register(
            'foo', function () {
                return new \stdClass();
            }, false
        );

        $this->assertEquals(new \stdClass(), $container->get('foo'));
    }

    public function testSharedClosure()
    {
        $container = new Container();
        $container->register(
            'foo', function () {
                return new \stdClass();
            }, true
        );

        $obj = $container->get('foo');
        $obj->foo = 123;
        $this->assertEquals($obj, $container->get('foo'));
    }

    public function testDefinition()
    {
        $component = $this->getMock('\Moss\Container\ComponentInterface');
        $component
            ->expects($this->any())
            ->method('__invoke')
            ->will($this->returnValue(new \stdClass()));

        $container = new Container();
        $container->register('foo', $component, false);

        $this->assertEquals(new \stdClass(), $container->get('foo'));
    }

    public function testSharedDefinition()
    {
        $component = $this->getMock('\Moss\Container\ComponentInterface');
        $component
            ->expects($this->any())
            ->method('__invoke')
            ->will($this->returnValue(new \stdClass()));

        $container = new Container();
        $container->register('foo', $component, true);

        $obj = $container->get('foo');
        $obj->foo = 123;
        $this->assertEquals($obj, $container->get('foo'));
    }

    public function testInstance()
    {
        $container = new Container();
        $container->register('foo', new \stdClass(), false);

        $obj = $container->get('foo');
        $obj->foo = 123;
        $this->assertEquals($obj, $container->get('foo'));
    }

    public function testUnregister()
    {
        $container = new Container();
        $container->register('foo', 'bar', true);
        $this->assertTrue($container->exists('foo'));
        $this->assertTrue($container->isShared('foo'));
        $container->Unregister('foo');
        $this->assertFalse($container->exists('foo'));
    }

    public function testUnregisterShared()
    {
        $container = new Container();
        $container->register('foo', 'bar', true);
        $this->assertTrue($container->exists('foo'));
        $this->assertTrue($container->isShared('foo'));
        $container->Unregister('foo');
        $this->assertFalse($container->exists('foo'));
    }

    public function testUnregisterInstance()
    {
        $container = new Container();
        $container->register('foo', new \stdClass());
        $this->assertTrue($container->exists('foo'));
        $this->assertTrue($container->isShared('foo'));
        $container->Unregister('foo');
        $this->assertFalse($container->exists('foo'));
    }
}

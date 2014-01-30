<?php
namespace moss\dispatcher;

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

class ListenerTest extends \PHPUnit_Framework_TestCase
{

    public function testGetNoMethod()
    {
        $container = $this->getContainerMock();

        $listener = new Listener('\moss\dispatcher\Foobar', null, array());
        $this->assertEquals(new \moss\dispatcher\Foobar(), $listener->get($container));
    }

    public function testGetNoArgs()
    {
        $container = $this->getContainerMock();

        $listener = new Listener('\moss\dispatcher\Foobar', 'foo', array());
        $this->assertEquals(new \moss\dispatcher\Foobar(), $listener->get($container));
    }

    public function testGetNormalArgs()
    {
        $container = $this->getContainerMock();

        $listener = new Listener('\moss\dispatcher\Foobar', 'foo', array('foo', 'bar', array('y', 'a', 'd', 'a')));
        $this->assertEquals(
            new \moss\dispatcher\Foobar('foo', 'bar', array('y', 'a', 'd', 'a')), $listener->get($container)
        );
    }

    public function testGetSpecial()
    {
        $container = $this->getContainerMock();

        $listener = new Listener('\moss\dispatcher\Foobar', 'foo', array('@Subject', '@Message'));
        $this->assertEquals(new \moss\dispatcher\Foobar('Subject', 'Message'), $listener->get($container, 'Subject', 'Message'));
    }

    protected function getContainerMock()
    {
        $container = $this->getMock('\moss\container\ContainerInterface');
        $container
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue(new \moss\dispatcher\Foobar()));

        return $container;
    }

    public function testGetContainerArgs()
    {
        $container = $this->getMock('\moss\container\ContainerInterface');
        $container
            ->expects($this->at(0))
            ->method('get')
            ->will($this->returnValue(new \moss\dispatcher\Foobar()));

        $container
            ->expects($this->at(1))
            ->method('get')
            ->will($this->returnValue(new \stdClass()));

        $listener = new Listener('\moss\dispatcher\Foobar', 'foo', array('@Foobar', '@Container'));
        $this->assertEquals(new \moss\dispatcher\Foobar(new \stdClass(), $container), $listener->get($container));
    }

    public function testCallable()
    {
        $container = $this->getContainerMock();

        $listener = new Listener('\moss\dispatcher\Foobar', 'foo', array('foo', 'bar', array('y', 'a', 'd', 'a')));
        $this->assertEquals($listener($container), $listener->get($container));
    }
}

<?php
namespace moss\dispatcher;


class ListenerTest extends \PHPUnit_Framework_TestCase
{

    public function testGetNoMethod()
    {
        $container = $this->getContainerMock();

        $listener = new Listener('\tests\moss\Foobar', null, array());
        $this->assertEquals(new \tests\moss\Foobar(), $listener->get($container));
    }

    public function testGetNoArgs()
    {
        $container = $this->getContainerMock();

        $listener = new Listener('\tests\moss\Foobar', 'foo', array());
        $this->assertEquals(new \tests\moss\Foobar(), $listener->get($container));
    }

    public function testGetNormalArgs()
    {
        $container = $this->getContainerMock();

        $listener = new Listener('\tests\moss\Foobar', 'foo', array('foo', 'bar', array('y', 'a', 'd', 'a')));
        $this->assertEquals(
            new \tests\moss\Foobar('foo', 'bar', array('y', 'a', 'd', 'a')), $listener->get($container)
        );
    }

    public function testGetSpecial()
    {
        $container = $this->getContainerMock();

        $listener = new Listener('\tests\moss\Foobar', 'foo', array('@Subject', '@Message'));
        $this->assertEquals(new \tests\moss\Foobar('Subject', 'Message'), $listener->get($container, 'Subject', 'Message'));
    }

    protected function getContainerMock()
    {
        $container = $this->getMock('\moss\container\ContainerInterface');
        $container
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue(new \tests\moss\Foobar()));

        return $container;
    }

    public function testGetContainerArgs()
    {
        $container = $this->getMock('\moss\container\ContainerInterface');
        $container
            ->expects($this->at(0))
            ->method('get')
            ->will($this->returnValue(new \tests\moss\Foobar()));

        $container
            ->expects($this->at(1))
            ->method('get')
            ->will($this->returnValue(new \stdClass()));

        $listener = new Listener('\tests\moss\Foobar', 'foo', array('@Foobar', '@Container'));
        $this->assertEquals(new \tests\moss\Foobar(new \stdClass(), $container), $listener->get($container));
    }
}

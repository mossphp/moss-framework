<?php
namespace moss\dispatcher;


class DispatcherTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \moss\dispatcher\DispatcherException
     * @expectedExceptionMessage Invalid event listener. Only callables or ListenerInterface instances can be registered, got "string"
     */
    public function testRegisterInvalidElement()
    {
        $Dispatcher = new Dispatcher();
        $Dispatcher->register('foo', 'foo');
    }

    public function testRegisterSingleEvent()
    {
        $Dispatcher = new Dispatcher();
        $result = $Dispatcher->register(
            'foo', function () {
            }
        );

        $this->assertInstanceOf('moss\dispatcher\Dispatcher', $result);
        $this->assertAttributeCount(1, 'events', $Dispatcher);
    }

    public function testRegisterMultipleEvents()
    {
        $Dispatcher = new Dispatcher();
        $result = $Dispatcher->register(
            array('foo', 'bar'), function () {
            }
        );

        $this->assertInstanceOf('moss\dispatcher\Dispatcher', $result);
        $this->assertAttributeCount(2, 'events', $Dispatcher);
    }

    public function testRegisterPriority()
    {
        $Dispatcher = new Dispatcher();
        $result = $Dispatcher
            ->register(
                'foo', function () {
                }, 1
            )
            ->register(
                'foo', function () {
                }, 0
            );


        $this->assertInstanceOf('moss\dispatcher\Dispatcher', $result);
        $this->assertAttributeCount(1, 'events', $Dispatcher);
    }

    public function testFireEmptyEvent()
    {
        $Dispatcher = new Dispatcher();
        $this->assertNull($Dispatcher->fire('foo'));
    }

    public function testFireClosureEvent()
    {
        $Dispatcher = new Dispatcher();
        $Dispatcher->register(
            'foo', function () {
                return 'foo';
            }
        );
        $this->assertEquals('foo', $Dispatcher->fire('foo'));
    }

    public function testFireListenerEvent()
    {
        $Listener = $this->getMock('\moss\dispatcher\ListenerInterface');
        $Listener
            ->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue('foo'));

        $Dispatcher = new Dispatcher($this->getMock('\moss\container\ContainerInterface'));
        $Dispatcher->register('foo', $Listener);
        $this->assertEquals('foo', $Dispatcher->fire('foo'));
    }

    public function testStopEvent()
    {
        $Dispatcher = new Dispatcher();
        $Dispatcher
            ->register(
                'foo', function () use ($Dispatcher) {
                    $Dispatcher->stop();

                    return 'foo';
                }
            )
            ->register(
                'foo', function () {
                    return 'bar';
                }
            );

        $this->assertEquals('foo', $Dispatcher->fire('foo'));
    }

    public function testFireBefore()
    {
        $Dispatcher = new Dispatcher();
        $Dispatcher->register(
            'foo:before', function () {
                return 'foo';
            }
        );
        $this->assertEquals('foo', $Dispatcher->fire('foo'));
    }

    public function testFireAfter()
    {
        $Dispatcher = new Dispatcher();
        $Dispatcher->register(
            'foo:after', function () {
                return 'foo';
            }
        );
        $this->assertEquals('foo', $Dispatcher->fire('foo'));
    }

    /**
     * @expectedException \Exception
     */
    public function testFireException()
    {
        $Dispatcher = new Dispatcher();
        $Dispatcher->register(
            'foo', function () {
                throw new \Exception('forced');
            }
        );
        $this->assertEquals('foo', $Dispatcher->fire('foo'));
    }

    public function testFireHandleException()
    {
        $Dispatcher = new Dispatcher();
        $Dispatcher->register(
            'foo', function () {
                throw new \Exception('forced');
            }
        );
        $Dispatcher->register(
            'foo:exception', function () {
                return 'foo';
            }
        );
        $this->assertEquals('foo', $Dispatcher->fire('foo'));
    }

    public function testFire()
    {
        $Dispatcher = new Dispatcher();
        $Dispatcher->register(
            'foo:before', function ($Container, $Subject) {
                return $Subject . ':before';
            }
        );
        $Dispatcher->register(
            'foo', function ($Container, $Subject) {
                return $Subject . ':event';
            }
        );
        $Dispatcher->register(
            'foo:after', function ($Container, $Subject) {
                return $Subject . ':after';
            }
        );
        $this->assertEquals('Subject:before:event:after', $Dispatcher->fire('foo', 'Subject'));
    }
}

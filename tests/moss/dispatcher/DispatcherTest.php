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
        $dispatcher = new Dispatcher();
        $dispatcher->register('foo', 'foo');
    }

    public function testRegisterSingleEvent()
    {
        $dispatcher = new Dispatcher();
        $result = $dispatcher->register(
            'foo', function () {
            }
        );

        $this->assertInstanceOf('moss\dispatcher\Dispatcher', $result);
        $this->assertAttributeCount(1, 'events', $dispatcher);
    }

    public function testRegisterMultipleEvents()
    {
        $dispatcher = new Dispatcher();
        $result = $dispatcher->register(
            array('foo', 'bar'), function () {
            }
        );

        $this->assertInstanceOf('moss\dispatcher\Dispatcher', $result);
        $this->assertAttributeCount(2, 'events', $dispatcher);
    }

    public function testRegisterPriority()
    {
        $dispatcher = new Dispatcher();
        $result = $dispatcher
            ->register(
                'foo', function () {
                }, 1
            )
            ->register(
                'foo', function () {
                }, 0
            );


        $this->assertInstanceOf('moss\dispatcher\Dispatcher', $result);
        $this->assertAttributeCount(1, 'events', $dispatcher);
    }

    public function testFireEmptyEvent()
    {
        $dispatcher = new Dispatcher();
        $this->assertNull($dispatcher->fire('foo'));
    }

    public function testFireClosureEvent()
    {
        $dispatcher = new Dispatcher();
        $dispatcher->register(
            'foo', function () {
                return 'foo';
            }
        );
        $this->assertEquals('foo', $dispatcher->fire('foo'));
    }

    public function testFireListenerEvent()
    {
        $listener = $this->getMock('\moss\dispatcher\ListenerInterface');
        $listener
            ->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue('foo'));

        $dispatcher = new Dispatcher($this->getMock('\moss\container\ContainerInterface'));
        $dispatcher->register('foo', $listener);
        $this->assertEquals('foo', $dispatcher->fire('foo'));
    }

    public function testStopEvent()
    {
        $dispatcher = new Dispatcher();
        $dispatcher
            ->register(
                'foo', function () use ($dispatcher) {
                    $dispatcher->stop();

                    return 'foo';
                }
            )
            ->register(
                'foo', function () {
                    return 'bar';
                }
            );

        $this->assertEquals('foo', $dispatcher->fire('foo'));
    }

    public function testFireBefore()
    {
        $dispatcher = new Dispatcher();
        $dispatcher->register(
            'foo:before', function () {
                return 'foo';
            }
        );
        $this->assertEquals('foo', $dispatcher->fire('foo'));
    }

    public function testFireAfter()
    {
        $dispatcher = new Dispatcher();
        $dispatcher->register(
            'foo:after', function () {
                return 'foo';
            }
        );
        $this->assertEquals('foo', $dispatcher->fire('foo'));
    }

    /**
     * @expectedException \Exception
     */
    public function testFireException()
    {
        $dispatcher = new Dispatcher();
        $dispatcher->register(
            'foo', function () {
                throw new \Exception('forced');
            }
        );
        $this->assertEquals('foo', $dispatcher->fire('foo'));
    }

    public function testFireHandleException()
    {
        $dispatcher = new Dispatcher();
        $dispatcher->register(
            'foo', function () {
                throw new \Exception('forced');
            }
        );
        $dispatcher->register(
            'foo:exception', function () {
                return 'foo';
            }
        );
        $this->assertEquals('foo', $dispatcher->fire('foo'));
    }

    public function testFire()
    {
        $dispatcher = new Dispatcher();
        $dispatcher->register(
            'foo:before', function ($container, $subject) {
                return $subject . ':before';
            }
        );
        $dispatcher->register(
            'foo', function ($container, $subject) {
                return $subject . ':event';
            }
        );
        $dispatcher->register(
            'foo:after', function ($container, $subject) {
                return $subject . ':after';
            }
        );
        $this->assertEquals('Subject:before:event:after', $dispatcher->fire('foo', 'Subject'));
    }
}

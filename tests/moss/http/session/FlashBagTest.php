<?php
namespace moss\http\session;

/**
 * @package Moss Test
 */
class FlashBagTest extends \PHPUnit_Framework_TestCase
{
    protected $session;

    public function testCount()
    {
        $bag = new FlashBag($this->sessionMock());
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');
        $this->assertEquals(2, $bag->count());
    }

    public function testReset()
    {
        $bag = new FlashBag($this->sessionMock());
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');
        $bag->reset();

        $this->assertEquals(0, $bag->count());
    }

    public function testHasAny()
    {
        $bag = new FlashBag($this->sessionMock());

        $this->assertFalse($bag->has());

        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');

        $this->assertTrue($bag->has());
    }

    public function testHasType()
    {
        $bag = new FlashBag($this->sessionMock());
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');

        $this->assertTrue($bag->has('bar'));
        $this->assertFalse($bag->has('boing'));
    }

    public function testGetAll()
    {
        $bag = new FlashBag($this->sessionMock());
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');

        $result = array(
            array('message' => 'foo', 'type' => 'bar'),
            array('message' => 'yada', 'type' => 'yada'),
        );

        $this->assertEquals($result, $bag->get());
    }

    public function testGetType()
    {
        $bag = new FlashBag($this->sessionMock());
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');

        $result = array(
            array('message' => 'foo', 'type' => 'bar'),
        );

        $this->assertEquals($result, $bag->get('bar'));
    }

    public function testRetrieve()
    {
        $bag = new FlashBag($this->sessionMock());
        $bag->add('foo', 'bar');
        $bag->add('yada', 'yada');

        $result = array(
            array('message' => 'foo', 'type' => 'bar'),
            array('message' => 'yada', 'type' => 'yada'),
        );

        $this->assertEquals($result[0], $bag->retrieve());
        $this->assertEquals($result[1], $bag->retrieve());
        $this->assertEquals(0, $bag->count());
    }

    protected function sessionMock()
    {
        $session = & $this->session;

        $mock = $this->getMock('\moss\http\session\SessionInterface');
        $mock
            ->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnCallback(array($this, 'sessionMockGet')));

        $mock = $this->getMock('\moss\http\session\SessionInterface');
        $mock
            ->expects($this->any())
            ->method('offsetSet')
            ->will($this->returnCallback(array($this, 'sessionMockSet')));

        return $session;
    }

    protected function & sessionMockGet($offset = null) {
        if($offset === null) {
            return $this->session;
        }

        return $this->session[$offset];
    }

    protected function & sessionMockSet($offset, $value) {
        return $this->session[$offset] = $value;
    }

}

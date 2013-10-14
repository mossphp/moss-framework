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
        $Bag = new FlashBag($this->sessionMock());
        $Bag->add('foo', 'bar');
        $Bag->add('yada', 'yada');
        $this->assertEquals(2, $Bag->count());
    }

    public function testReset()
    {
        $Bag = new FlashBag($this->sessionMock());
        $Bag->add('foo', 'bar');
        $Bag->add('yada', 'yada');
        $Bag->reset();

        $this->assertEquals(0, $Bag->count());
    }

    public function testHasAny()
    {
        $Bag = new FlashBag($this->sessionMock());

        $this->assertFalse($Bag->has());

        $Bag->add('foo', 'bar');
        $Bag->add('yada', 'yada');

        $this->assertTrue($Bag->has());
    }

    public function testHasType()
    {
        $Bag = new FlashBag($this->sessionMock());
        $Bag->add('foo', 'bar');
        $Bag->add('yada', 'yada');

        $this->assertTrue($Bag->has('bar'));
        $this->assertFalse($Bag->has('boing'));
    }

    public function testGetAll()
    {
        $Bag = new FlashBag($this->sessionMock());
        $Bag->add('foo', 'bar');
        $Bag->add('yada', 'yada');

        $result = array(
            array('message' => 'foo', 'type' => 'bar'),
            array('message' => 'yada', 'type' => 'yada'),
        );

        $this->assertEquals($result, $Bag->get());
    }

    public function testGetType()
    {
        $Bag = new FlashBag($this->sessionMock());
        $Bag->add('foo', 'bar');
        $Bag->add('yada', 'yada');

        $result = array(
            array('message' => 'foo', 'type' => 'bar'),
        );

        $this->assertEquals($result, $Bag->get('bar'));
    }

    public function testRetrieve()
    {
        $Bag = new FlashBag($this->sessionMock());
        $Bag->add('foo', 'bar');
        $Bag->add('yada', 'yada');

        $result = array(
            array('message' => 'foo', 'type' => 'bar'),
            array('message' => 'yada', 'type' => 'yada'),
        );

        $this->assertEquals($result[0], $Bag->retrieve());
        $this->assertEquals($result[1], $Bag->retrieve());
        $this->assertEquals(0, $Bag->count());
    }

    protected function sessionMock()
    {
        $session = & $this->session;

        $Mock = $this->getMock('\moss\http\session\SessionInterface');
        $Mock
            ->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnCallback(array($this, 'sessionMockGet')));

        $Mock = $this->getMock('\moss\http\session\SessionInterface');
        $Mock
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

<?php
namespace Moss\Http\Response;


class CookieBagTest extends \PHPUnit_Framework_TestCase
{

    public function testGetSet()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('name')->willReturn('foo');

        $bag = new CookieBag();
        $bag->set($cookie);
        $this->assertEquals($cookie, $bag->get('foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Value must be an instance of CookieInterface
     */
    public function testSetInvalidInstanceAsOffset()
    {
        $bag = new CookieBag();
        $bag->set(new \stdClass());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Value must be an instance of CookieInterface
     */
    public function testSetInvalidInstanceAsValue()
    {
        $bag = new CookieBag();
        $bag->set('foo', new \stdClass());
    }

    public function testGetWithDefaultValue()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');

        $bag = new CookieBag();
        $this->assertEquals($cookie, $bag->get('foo', $cookie));
    }

    public function testGetAll()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('name')->willReturn('foo');

        $bag = new CookieBag();
        $bag->set($cookie);
        $this->assertEquals(['foo' => $cookie], $bag->get());
    }

    public function testHasWithoutParam()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('name')->willReturn('foo');

        $bag = new CookieBag();
        $bag->set($cookie);
        $this->assertTrue($bag->has());
    }

    public function testHas()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('name')->willReturn('foo');

        $bag = new CookieBag();
        $bag->set($cookie);
        $this->assertTrue($bag->has('foo'));
    }

    public function testAll()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('name')->willReturn('foo');

        $bag = new CookieBag();
        $bag->set($cookie);
        $this->assertEquals(['foo' => $cookie], $bag->all());
    }

    public function testAllReplace()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('name')->willReturn('foo');

        $bag = new CookieBag();
        $bag->all([$cookie]);
        $this->assertEquals(['foo' => $cookie], $bag->all());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Value must be an instance of CookieInterface
     */
    public function testAllReplaceInvalidInstances()
    {
        $bag = new CookieBag();
        $bag->all([new \stdClass()]);
    }

    public function testRemove()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('name')->willReturn('foo');

        $bag = new CookieBag();
        $bag->set($cookie);
        $this->assertEquals(['foo' => $cookie], $bag->all());
        $bag->remove('foo');
        $this->assertEquals([], $bag->all());
    }

    public function testRemoveAll()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('name')->willReturn('foo');

        $bag = new CookieBag();
        $bag->set($cookie);
        $this->assertEquals(['foo' => $cookie], $bag->all());
        $bag->remove();
        $this->assertEquals([], $bag->all());
    }

    public function testReset()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('name')->willReturn('foo');

        $bag = new CookieBag();
        $bag->set($cookie);
        $this->assertEquals(['foo' => $cookie], $bag->all());
        $bag->reset();
        $this->assertEquals([], $bag->all());
    }

    public function testOffsetUnset()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('name')->willReturn('foo');

        $bag = new CookieBag();
        $bag['foo'] = $cookie;
        unset($bag['foo']);
        $this->assertEquals(0, $bag->count());
    }

    public function testOffsetGetSet()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('name')->willReturn('foo');

        $bag = new CookieBag();
        $bag['foo'] = $cookie;
        $this->assertEquals($cookie, $bag['foo']);
    }

    public function testOffsetGetEmpty()
    {
        $bag = new CookieBag();
        $this->assertNull(null, $bag['foo']);
    }

    public function testOffsetSetWithoutKey()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('name')->willReturn('foo');

        $bag = new CookieBag();
        $bag[] = $cookie;
        $this->assertEquals($cookie, $bag['foo']);
    }

    public function testOffsetExists()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('name')->willReturn('foo');

        $bag = new CookieBag();
        $bag['bar'] = $cookie;
        $this->assertTrue(isset($bag['bar']));
    }

    public function testIterator()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('name')->willReturn('foo');

        $bag = new CookieBag();
        $bag['foo'] = $cookie;

        foreach ($bag as $key => $val) {
            $this->assertEquals($key, 'foo');
            $this->assertEquals($val, $cookie);
        }
    }

    public function testCount()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('name')->willReturn('foo');

        $bag = new CookieBag();
        $bag['foo'] = $cookie;
        $bag['bar'] = $cookie;
        $this->assertEquals(2, $bag->count());
    }

    public function testAsArray()
    {
        $expected = [
            'Set-Cookie: MockedCookieString'
        ];

        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('name')->willReturn('foo');
        $cookie->expects($this->any())->method('__toString')->willReturn('MockedCookieString');

        $bag = new CookieBag();
        $bag->set($cookie);
        $this->assertEquals($expected, $bag->asArray());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Value must be an instance of CookieInterface
     */
    public function testOffsetSet()
    {
        $bag = new CookieBag();
        $bag['foo'] = 'yada';
    }
}

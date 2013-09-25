<?php
namespace moss\security;


class TokenTest extends \PHPUnit_Framework_TestCase
{


    public function testCredentials()
    {
        $Token = new Token('foo', 'bar');
        $this->assertEquals(array('login' => 'foo', 'password' => 'bar'), $Token->credentials());
    }

    public function testRemove()
    {
        $Token = new Token('foo', 'bar');
        $this->assertEquals(array('login' => 'foo', 'password' => 'bar'), $Token->credentials());

        $Token->remove();
        $this->assertNull($Token->credentials());
    }

    public function testIsAuthenticated()
    {
        $Token = new Token();
        $this->assertFalse($Token->isAuthenticated());

        $Token->authenticate('foobar');
        $this->assertTrue($Token->isAuthenticated());
    }

    public function testAuthenticate()
    {
        $Token = new Token();
        $this->assertFalse($Token->isAuthenticated());

        $Token->authenticate('foobar');
        $this->assertEquals('foobar', $Token->authenticate());
    }

    public function testUser()
    {
        $Token = new Token();
        $this->assertFalse($Token->isAuthenticated());

        $Token->user('123');
        $this->assertEquals('123', $Token->user());
    }

    public function testSerialize()
    {
        $Token = new Token();
        $this->assertInternalType('string', serialize($Token));
    }

    public function testUnserialize()
    {
        $Token = new Token();
        $Token->authenticate('foo');
        $Token->user('bar');
        $Token = unserialize(serialize($Token));
        $this->assertEquals('foo', $Token->authenticate());
        $this->assertEquals('bar', $Token->user());

    }


}

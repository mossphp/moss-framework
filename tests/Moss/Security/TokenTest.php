<?php
namespace Moss\Security;


class TokenTest extends \PHPUnit_Framework_TestCase
{


    public function testCredentials()
    {
        $token = new Token('foo', 'bar');
        $this->assertEquals(['auth' => 'foo', 'user' => 'bar'], $token->credentials());
    }

    public function testRemove()
    {

        $token = new Token('foo', 'bar');
        $this->assertEquals(['auth' => 'foo', 'user' => 'bar'], $token->credentials());

        $token->remove();

        $this->assertEquals(['auth' => null, 'user' => null], $token->credentials());
    }

    public function testIsAuthenticated()
    {
        $token = new Token();
        $this->assertFalse($token->isAuthenticated());

        $token->authenticate('foobar');
        $this->assertTrue($token->isAuthenticated());
    }

    public function testAuthenticate()
    {
        $token = new Token();
        $this->assertFalse($token->isAuthenticated());

        $token->authenticate('foobar');
        $this->assertEquals('foobar', $token->authenticate());
    }

    public function testUser()
    {
        $token = new Token();
        $this->assertFalse($token->isAuthenticated());

        $token->user('123');
        $this->assertEquals('123', $token->user());
    }

    public function testSerialize()
    {
        $token = new Token();
        $this->assertInternalType('string', serialize($token));
    }

    public function testUnserialize()
    {
        $token = new Token();
        $token->authenticate('foo');
        $token->user('bar');
        $token = unserialize(serialize($token));
        $this->assertEquals('foo', $token->authenticate());
        $this->assertEquals('bar', $token->user());

    }


}

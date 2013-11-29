<?php
namespace moss\security;


class TokenStashTest extends \PHPUnit_Framework_TestCase
{


    public function testTokenRetrieval()
    {
        $token = $this->getMock('\moss\security\TokenInterface');

        $stash = new TokenStash($this->mockSession());

        $stash->put($token);
        $this->assertEquals($token, $stash->get());
    }

    public function testTokenDestroy()
    {
        $token = $this->getMock('\moss\security\TokenInterface');

        $stash = new TokenStash($this->mockSession());

        $stash->put($token);
        $this->assertEquals($token, $stash->get());

        $stash->destroy();
        $this->assertNull($stash->get());
    }

    protected function mockSession()
    {
        $arr = array();
        $mock = $this->getMock('\moss\http\session\SessionInterface');
        $mock
            ->expects($this->any())
            ->method('set')
            ->will($this->returnCallback(function ($offset, $token) use (&$arr) { $arr[$offset] = $token; }));

        $mock
            ->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($offset) use (&$arr) { return isset($arr[$offset]) ? $arr[$offset] : null; }));

        $mock
            ->expects($this->any())
            ->method('remove')
            ->will($this->returnCallback(function () use (&$arr) { return $arr = array(); }));

        return $mock;
    }
}
 
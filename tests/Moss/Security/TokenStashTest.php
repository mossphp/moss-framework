<?php
namespace Moss\Security;


class TokenStashTest extends \PHPUnit_Framework_TestCase
{


    public function testTokenStashing()
    {
        $token = $this->getMock('\Moss\Security\TokenInterface');

        $session = $this->getMock('\Moss\Http\Session\SessionInterface');
        $session->expects($this->at(0))->method('regenerate');
        $session->expects($this->at(1))->method('set')->with('token', $token);

        $stash = new TokenStash($session);
        $stash->put($token);
    }

    public function testTokenRetrieval()
    {
        $token = $this->getMock('\Moss\Security\TokenInterface');

        $session = $this->getMock('\Moss\Http\Session\SessionInterface');
        $session->expects($this->once())->method('get')->will($this->returnValue($token));

        $stash = new TokenStash($session);
        $this->assertEquals($token, $stash->get());
    }

    public function testTokenDestroy()
    {
        $token = $this->getMock('\Moss\Security\TokenInterface');

        $session = $this->getMock('\Moss\Http\Session\SessionInterface');
        $session->expects($this->once())->method('remove');
        $session->expects($this->at(0))->method('get')->will($this->returnValue($token));
        $session->expects($this->at(2))->method('get')->will($this->returnValue(null));

        $stash = new TokenStash($session);
        $this->assertEquals($token, $stash->get());

        $stash->destroy();
        $this->assertNull($stash->get());
    }
}
 
<?php
namespace moss\security;


class SecurityTest extends \PHPUnit_Framework_TestCase
{
    public function testTokenize()
    {
        $Security = new Security($this->getMock('\moss\security\TokenStashInterface'));
        $Security->registerUserProvider($this->mockProvider(true, true));
        $Security->tokenize(array('foo', 'bar'));
    }

    /**
     * @expectedException \moss\security\AuthenticationException
     * @expectedExceptionMessage Unable to tokenize, empty credentials
     */
    public function testTokenizeWithEmptyCredentials()
    {
        $Security = new Security($this->getMock('\moss\security\TokenStashInterface'));
        $Security->registerUserProvider($this->mockProvider(true, false));
        $Security->tokenize(array());
    }

    /**
     * @expectedException \moss\security\AuthenticationException
     * @expectedExceptionMessage Credentials could not be tokenized in provider
     */
    public function testTokenizeFailure()
    {
        $Security = new Security($this->getMock('\moss\security\TokenStashInterface'));
        $Security->registerUserProvider($this->mockProvider(true, false));
        $Security->tokenize(array('foo', 'bar'));
    }

    /**
     * @expectedException \moss\security\AuthenticationException
     * @expectedExceptionMessage Missing provider supporting credentials
     */
    public function testTokenizeMissingProvider()
    {
        $Security = new Security($this->getMock('\moss\security\TokenStashInterface'));
        $Security->registerUserProvider($this->mockProvider(false, false));
        $Security->tokenize(array('foo', 'bar'));
    }

    /**
     * @expectedException \moss\security\AuthenticationException
     * @expectedExceptionMessage Unable to authenticate, token is missing
     */
    public function testAuthWithoutToken()
    {
        $Security = new Security($this->getMock('\moss\security\TokenStashInterface'));
        $Security->registerArea($this->mockArea());
        $Security->registerUserProvider($this->mockProvider(true, false));
        $Security->authenticate($this->getMock('\moss\http\request\RequestInterface'));
    }

    /**
     * @expectedException \moss\security\AuthenticationException
     * @expectedExceptionMessage Token could not be authenticated in provider
     */
    public function testAuthFailure()
    {
        $Security = new Security($this->mockStash());
        $Security->registerArea($this->mockArea());
        $Security->registerUserProvider($this->mockProvider(true, false));
        $Security->authenticate($this->getMock('\moss\http\request\RequestInterface'));
    }

    /**
     * @expectedException \moss\security\AuthenticationException
     * @expectedExceptionMessage Missing provider supporting token
     */
    public function testAuthWithoutMatchingProvider()
    {
        $Security = new Security($this->mockStash());
        $Security->registerArea($this->mockArea());
        $Security->registerUserProvider($this->mockProvider(false, false));
        $Security->authenticate($this->getMock('\moss\http\request\RequestInterface'));
    }

    public function testAuthSuccess()
    {
        $Security = new Security($this->mockStash());
        $Security->registerUserProvider($this->mockProvider(false, false));
        $Security->registerUserProvider($this->mockProvider());
        $Security->registerArea($this->mockArea(false, false));
        $Security->registerArea($this->mockArea(true, true));
        $Security->authenticate($this->getMock('\moss\http\request\RequestInterface'));
        $Security->authorize($this->getMock('\moss\http\request\RequestInterface'));
    }

    public function testAuthSuccessNoAreas()
    {
        $Security = new Security($this->mockStash());
        $this->assertInstanceOf('\moss\security\SecurityInterface', $Security->authorize($this->getMock('\moss\http\request\RequestInterface')));
    }

    /**
     * @expectedException \moss\security\AuthorizationException
     * @expectedExceptionMessage Access denied to area "sample_area". Authenticated user does not have access
     */
    public function testAuthorizeDenied()
    {
        $Security = new Security($this->mockStash());
        $Security->registerUserProvider($this->mockProvider(false, true));
        $Security->registerUserProvider($this->mockProvider(true, true));
        $Security->registerArea($this->mockArea(true, false));
        $Security->authenticate($this->getMock('\moss\http\request\RequestInterface'));
        $Security->authorize($this->getMock('\moss\http\request\RequestInterface'));
    }

    public function testStash()
    {
        $Security = new Security($this->mockStash());
        $this->assertInstanceOf('\moss\security\TokenStashInterface', $Security->stash());
    }

    public function testToken()
    {
        $Security = new Security($this->mockStash());
        $this->assertInstanceOf('\moss\security\TokenInterface', $Security->token());
    }

    public function testUser()
    {
        $Security = new Security($this->mockStash());
        $Security->registerUserProvider($this->mockProvider(true, true));
        $Security->registerArea($this->mockArea(true, true));
        $Security->authenticate($this->getMock('\moss\http\request\RequestInterface'));

        $this->assertInstanceOf('\moss\security\UserInterface', $Security->user());
    }

    public function testUserNoAuth()
    {
        $Security = new Security($this->mockStash());
        $this->assertNull($Security->user());
    }

    protected function mockStash()
    {
        $Token = $this->getMock('\moss\security\TokenInterface');

        $Stash = $this->getMock('\moss\security\TokenStashInterface');
        $Stash
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($Token));

        return $Stash;
    }

    protected function mockProvider($support = true, $auth = true)
    {
        $Provider = $this->getMock('\moss\security\UserProviderInterface');

        $Provider
            ->expects($this->any())
            ->method('supportsCredentials')
            ->will($this->returnValue($support));

        $Provider
            ->expects($this->any())
            ->method('tokenize')
            ->will($this->returnValue($auth ? $this->getMock('\moss\security\TokenInterface') : false));

        $Provider
            ->expects($this->any())
            ->method('supportsToken')
            ->will($this->returnValue($support));

        $Provider
            ->expects($this->any())
            ->method('authenticate')
            ->will($this->returnValue($auth));

        $Provider
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->getMock('\moss\security\UserInterface')));

        return $Provider;
    }

    protected function mockArea($match = true, $access = true)
    {
        $Area = $this->getMock('\moss\security\AreaInterface');
        $Area
            ->expects($this->any())
            ->method('pattern')
            ->will($this->returnValue('sample_area'));

        $Area
            ->expects($this->any())
            ->method('match')
            ->will($this->returnValue($match));

        $Area
            ->expects($this->any())
            ->method('authorize')
            ->will($this->returnValue($access));

        return $Area;
    }
}

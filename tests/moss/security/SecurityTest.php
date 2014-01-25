<?php
namespace moss\security;


class SecurityTest extends \PHPUnit_Framework_TestCase
{
    public function testTokenize()
    {
        $security = new Security($this->getMock('\moss\security\TokenStashInterface'));
        $security->registerUserProvider($this->mockProvider(true, true));
        $security->tokenize(array('foo', 'bar'));
    }

    /**
     * @expectedException \moss\security\AuthenticationException
     * @expectedExceptionMessage Unable to create token, no or empty credentials
     */
    public function testTokenizeWithEmptyCredentials()
    {
        $security = new Security($this->getMock('\moss\security\TokenStashInterface'));
        $security->registerUserProvider($this->mockProvider(true, false));
        $security->tokenize(array());
    }

    /**
     * @expectedException \moss\security\AuthenticationException
     * @expectedExceptionMessage Unable to create token, credentials could not be authenticated in provider
     */
    public function testTokenizeFailure()
    {
        $security = new Security($this->getMock('\moss\security\TokenStashInterface'));
        $security->registerUserProvider($this->mockProvider(true, false));
        $security->tokenize(array('foo', 'bar'));
    }

    /**
     * @expectedException \moss\security\AuthenticationException
     * @expectedExceptionMessage Unable to create token, missing provider supporting credentials
     */
    public function testTokenizeMissingProvider()
    {
        $security = new Security($this->getMock('\moss\security\TokenStashInterface'));
        $security->registerUserProvider($this->mockProvider(false, false));
        $security->tokenize(array('foo', 'bar'));
    }

    /**
     * @expectedException \moss\security\AuthenticationException
     * @expectedExceptionMessage Unable to authenticate, token is missing
     */
    public function testAuthWithoutToken()
    {
        $security = new Security($this->getMock('\moss\security\TokenStashInterface'));
        $security->registerArea($this->mockArea());
        $security->registerUserProvider($this->mockProvider(true, false));
        $security->authenticate($this->getMock('\moss\http\request\RequestInterface'));
    }

    /**
     * @expectedException \moss\security\AuthenticationException
     * @expectedExceptionMessage Token could not be authenticated in provider
     */
    public function testAuthFailure()
    {
        $security = new Security($this->mockStash(true));
        $security->registerArea($this->mockArea());
        $security->registerUserProvider($this->mockProvider(true, false));
        $security->authenticate($this->getMock('\moss\http\request\RequestInterface'));
    }

    /**
     * @expectedException \moss\security\AuthenticationException
     * @expectedExceptionMessage Missing provider supporting token
     */
    public function testAuthWithoutMatchingProvider()
    {
        $security = new Security($this->mockStash(true));
        $security->registerArea($this->mockArea());
        $security->registerUserProvider($this->mockProvider(false, false));
        $security->authenticate($this->getMock('\moss\http\request\RequestInterface'));
    }

    public function testAuthSuccess()
    {
        $security = new Security($this->mockStash(true));
        $security->registerUserProvider($this->mockProvider(false, false));
        $security->registerUserProvider($this->mockProvider());
        $security->registerArea($this->mockArea(false, false));
        $security->registerArea($this->mockArea(true, true));
        $security->authenticate($this->getMock('\moss\http\request\RequestInterface'));
        $security->authorize($this->getMock('\moss\http\request\RequestInterface'));
    }

    public function testAuthSuccessNoAreas()
    {
        $security = new Security($this->mockStash());
        $this->assertInstanceOf('\moss\security\SecurityInterface', $security->authorize($this->getMock('\moss\http\request\RequestInterface')));
    }

    /**
     * @expectedException \moss\security\AuthorizationException
     * @expectedExceptionMessage Access denied to area "sample_area". Authenticated user does not have access
     */
    public function testAuthorizeDenied()
    {
        $security = new Security($this->mockStash(true));
        $security->registerUserProvider($this->mockProvider(false, true));
        $security->registerUserProvider($this->mockProvider(true, true));
        $security->registerArea($this->mockArea(true, false));
        $security->authenticate($this->getMock('\moss\http\request\RequestInterface'));
        $security->authorize($this->getMock('\moss\http\request\RequestInterface'));
    }

    public function testStash()
    {
        $security = new Security($this->mockStash());
        $this->assertInstanceOf('\moss\security\TokenStashInterface', $security->stash());
    }

    public function testToken()
    {
        $security = new Security($this->mockStash(true));
        $this->assertInstanceOf('\moss\security\TokenInterface', $security->token());
    }

    public function testUser()
    {
        $security = new Security($this->mockStash(true));
        $security->registerUserProvider($this->mockProvider(true, true));
        $security->registerArea($this->mockArea(true, true));
        $security->authenticate($this->getMock('\moss\http\request\RequestInterface'));

        $this->assertInstanceOf('\moss\security\UserInterface', $security->user());
    }

    public function testUserNoAuth()
    {
        $security = new Security($this->mockStash(true));
        $this->assertNull($security->user());
    }

    public function testDestroy()
    {
        $security = new Security($this->mockStash(true));
        $security->registerUserProvider($this->mockProvider(true, true));
        $security->registerArea($this->mockArea(true, true));
        $security->authenticate($this->getMock('\moss\http\request\RequestInterface'));

        $this->assertInstanceOf('\moss\security\UserInterface', $security->user());

        $security->destroy();

        $this->assertNull($security->user());
        $this->assertNull($security->token());
    }

    protected function mockStash($token = false)
    {
        $container = $token ? $this->getMock('\moss\security\TokenInterface') : null;

        $stash = $this->getMock('\moss\security\TokenStashInterface');
        $stash
            ->expects($this->any())
            ->method('put')
            ->will($this->returnCallback(function ($token) use (&$container) { $container = $token; }));

        $stash
            ->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function () use (&$container) { return $container; }));

        $stash
            ->expects($this->any())
            ->method('destroy')
            ->will($this->returnCallback(function () use (&$container) { $container = null; }));

        return $stash;
    }

    protected function mockProvider($support = true, $auth = true)
    {
        $provider = $this->getMock('\moss\security\UserProviderInterface');

        $provider
            ->expects($this->any())
            ->method('supportsCredentials')
            ->will($this->returnValue($support));

        $provider
            ->expects($this->any())
            ->method('tokenize')
            ->will($this->returnValue($auth ? $this->getMock('\moss\security\TokenInterface') : false));

        $provider
            ->expects($this->any())
            ->method('supportsToken')
            ->will($this->returnValue($support));

        $provider
            ->expects($this->any())
            ->method('authenticate')
            ->will($this->returnValue($auth));

        $provider
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->getMock('\moss\security\UserInterface')));

        return $provider;
    }

    protected function mockArea($match = true, $access = true)
    {
        $area = $this->getMock('\moss\security\AreaInterface');
        $area
            ->expects($this->any())
            ->method('pattern')
            ->will($this->returnValue('sample_area'));

        $area
            ->expects($this->any())
            ->method('match')
            ->will($this->returnValue($match));

        $area
            ->expects($this->any())
            ->method('authorize')
            ->will($this->returnValue($access));

        return $area;
    }
}

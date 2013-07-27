<?php
namespace Moss\security;


class SecurityTest extends \PHPUnit_Framework_TestCase {


	public function testRegisterUserProvider() {
		$Security = new Security($this->mockStash());
		$Security->registerUserProvider($this->mockUserProvider(false));
	}

	public function testRegisterArea() {
		$Security = new Security($this->mockStash());
		$Security->registerArea($this->mockArea(false, false));
	}

	public function testAuthenticated() {
		$Security = new Security($this->mockStash());
		$Security->registerUserProvider($this->mockUserProvider(false));
		$Security->registerUserProvider($this->mockUserProvider(true));
		$Security->authenticate();
	}

	/**
	 * @expectedException \Moss\security\AuthenticationException
	 */
	public function testAuthenticationFailed() {
		$Security = new Security($this->mockStash());
		$Security->registerUserProvider($this->mockUserProvider(true, false));
		$Security->authenticate();
	}

	public function testAuthorizeSuccessful() {
		$Security = new Security($this->mockStash());
		$Security->registerUserProvider($this->mockUserProvider(false, false));
		$Security->registerUserProvider($this->mockUserProvider());
		$Security->authenticate();
		$Security->registerArea($this->mockArea(false, false));
		$Security->registerArea($this->mockArea(true, true));
		$Security->authorize($this->getMock('\Moss\http\request\RequestInterface'));
	}

	public function testAuthorizeSuccessfulNoAreas() {
		$Security = new Security($this->mockStash());
		$Security->authorize($this->getMock('\Moss\http\request\RequestInterface'));
	}

	/**
	 * @expectedException \Moss\security\AuthorizationException
	 */
	public function testAuthorizeDenied() {
		$Security = new Security($this->mockStash());
		$Security->registerUserProvider($this->mockUserProvider(false, true));
		$Security->registerUserProvider($this->mockUserProvider(true, true));
		$Security->authenticate();
		$Security->registerArea($this->mockArea(true, false));
		$Security->authorize($this->getMock('\Moss\http\request\RequestInterface'));
	}

	public function testStash() {
		$Security = new Security($this->mockStash());
		$this->assertInstanceOf('\Moss\security\TokenStashInterface', $Security->stash());
	}

	public function testToken() {
		$Security = new Security($this->mockStash());
		$this->assertInstanceOf('\Moss\security\TokenInterface', $Security->token());
	}

	public function testUser() {
		$Security = new Security($this->mockStash());
		$Security->registerUserProvider($this->mockUserProvider(false, true));
		$Security->registerUserProvider($this->mockUserProvider(true, true));
		$Security->authenticate();
		$this->assertInstanceOf('\Moss\security\UserInterface', $Security->user());
	}

	public function testUserNoAuth() {
		$Security = new Security($this->mockStash());
		$this->assertNull($Security->user());
	}

	protected function mockStash() {
		$Token = $this->getMock('\Moss\security\TokenInterface');

		$Stash = $this->getMock('\Moss\security\TokenStashInterface');
		$Stash
			->expects($this->any())
			->method('get')
			->will($this->returnValue($Token));

		return $Stash;
	}

	protected function mockUserProvider($support = true, $auth = true) {
		$Provider = $this->getMock('\Moss\security\UserProviderInterface');
		$Provider
			->expects($this->any())
			->method('supports')
			->will($this->returnValue($support));

		$Provider
			->expects($this->any())
			->method('authenticate')
			->will($this->returnValue($auth ? $this->getMock('\Moss\security\UserInterface') : false));

		$Provider
			->expects($this->any())
			->method('get')
			->will($this->returnValue($this->getMock('\Moss\security\UserInterface')));

		return $Provider;
	}

	protected function mockArea($match = true, $access = true) {
		$Area = $this->getMock('\Moss\security\AreaInterface');
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

<?php
namespace Moss\security;


class AreaTest extends \PHPUnit_Framework_TestCase {

	public function testPattern() {
		$Area = new Area('Bundle:*:!login|logout');
		$this->assertEquals('Bundle:*:!login|logout', $Area->pattern());
	}

	public function testRoles() {
		$Area = new Area('Bundle:*:!login|logout', array('some', 'roles'));
		$this->assertEquals(array('some', 'roles'), $Area->roles());
	}

	public function testIps() {
		$Area = new Area('Bundle:*:!login|!logout', array(), array('127.0.0.1'));
		$this->assertEquals(array('127.0.0.1'), $Area->ips());
	}

	public function testMatch() {
		$Area = new Area('Bundle:*:!login|logout');

		$RequestBlock = $this->getMock('\Moss\http\request\RequestInterface');
		$RequestBlock->expects($this->any())->method('controller')->will($this->returnValue('Bundle:something:index'));
		$this->assertTrue($Area->match($RequestBlock));

		$RequestPass = $this->getMock('\Moss\http\request\RequestInterface');
		$RequestPass->expects($this->any())->method('controller')->will($this->returnValue('Bundle:something:login'));
		$this->assertFalse($Area->match($RequestPass));
	}

	public function testAuthUserFail() {
		$Area = new Area('Bundle:*:!login|logout', array('role'));

		$User = $this->getMock('\Moss\security\UserInterface');
		$User->expects($this->any())->method('hasRole')->will($this->returnValue(false));

		$this->assertFalse($Area->authorize($User));
	}
	public function testAuthUserPass() {
		$Area = new Area('Bundle:*:!login|logout', array('role'));

		$User = $this->getMock('\Moss\security\UserInterface');
		$User->expects($this->any())->method('hasRole')->will($this->returnValue(true));

		$this->assertTrue($Area->authorize($User));
	}

	public function testAuthIpFail() {
		$Area = new Area('Bundle:*:!login|logout', array(), array('127.0.0.1'));

		$User = $this->getMock('\Moss\security\UserInterface');
		$User->expects($this->any())->method('hasRole')->will($this->returnValue(true));

		$this->assertTrue($Area->authorize($User, '127.0.0.1'));
	}

	public function testAutIpPass() {
		$Area = new Area('Bundle:*:!login|logout', array(), array('127.0.0.1'));

		$User = $this->getMock('\Moss\security\UserInterface');
		$User->expects($this->any())->method('hasRole')->will($this->returnValue(true));

		$this->assertFalse($Area->authorize($User, '198.162.1.1'));
	}

}

<?php
namespace moss\security;


class AreaTest extends \PHPUnit_Framework_TestCase
{

    public function testPattern()
    {
        $Area = new Area('Bundle:*:!login|logout');
        $this->assertEquals('Bundle:*:!login|logout', $Area->pattern());
    }

    public function testRoles()
    {
        $Area = new Area('Bundle:*:!login|logout', array('some', 'roles'));
        $this->assertEquals(array('some', 'roles'), $Area->roles());
    }

    public function testIps()
    {
        $Area = new Area('Bundle:*:!login|!logout', array(), array('127.0.0.1'));
        $this->assertEquals(array('127.0.0.1'), $Area->ips());
    }

    public function testMatch()
    {
        $Area = new Area('Bundle:*:!login|logout');

        $RequestBlock = $this->getMock('\moss\http\request\RequestInterface');
        $RequestBlock
            ->expects($this->any())
            ->method('controller')
            ->will($this->returnValue('Bundle:something:index'));
        $this->assertTrue($Area->match($RequestBlock));

        $RequestPass = $this->getMock('\moss\http\request\RequestInterface');
        $RequestPass
            ->expects($this->any())
            ->method('controller')
            ->will($this->returnValue('Bundle:something:login'));
        $this->assertFalse($Area->match($RequestPass));
    }

    public function testAuthUserRoleFail()
    {
        $Area = new Area('Bundle:*:!login|logout', array('role'));

        $User = $this->getMock('\moss\security\UserInterface');
        $User
            ->expects($this->any())
            ->method('hasRole')
            ->will($this->returnValue(false));

        $this->assertFalse($Area->authorize($User));
    }

    public function testAuthUserIPFail()
    {
        $Area = new Area('Bundle:*:!login|logout', array(), array('127.0.0.1'));
        $User = $this->getMock('\moss\security\UserInterface');
        $this->assertFalse($Area->authorize($User, '127.0.0.2'));
    }

    public function testAuthUserRole()
    {
        $Area = new Area('Bundle:*:!login|logout', array('role'));

        $User = $this->getMock('\moss\security\UserInterface');
        $User
            ->expects($this->any())
            ->method('hasRole')
            ->will($this->returnValue(true));

        $this->assertTrue($Area->authorize($User));
    }

    public function testAuthUserIP()
    {
        $Area = new Area('Bundle:*:!login|logout', array(), array('127.0.0.1'));
        $User = $this->getMock('\moss\security\UserInterface');
        $this->assertTrue($Area->authorize($User, '127.0.0.1'));
    }
}

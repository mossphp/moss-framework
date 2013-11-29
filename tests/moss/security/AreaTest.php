<?php
namespace moss\security;


class AreaTest extends \PHPUnit_Framework_TestCase
{

    public function testPattern()
    {
        $area = new Area('Bundle:*:!login|logout');
        $this->assertEquals('Bundle:*:!login|logout', $area->pattern());
    }

    public function testRoles()
    {
        $area = new Area('Bundle:*:!login|logout', array('some', 'roles'));
        $this->assertEquals(array('some', 'roles'), $area->roles());
    }

    public function testIps()
    {
        $area = new Area('Bundle:*:!login|!logout', array(), array('127.0.0.1'));
        $this->assertEquals(array('127.0.0.1'), $area->ips());
    }

    public function testMatch()
    {
        $area = new Area('Bundle:*:!login|logout');

        $requestBlock = $this->getMock('\moss\http\request\RequestInterface');
        $requestBlock
            ->expects($this->any())
            ->method('controller')
            ->will($this->returnValue('Bundle:something:index'));
        $this->assertTrue($area->match($requestBlock));

        $requestPass = $this->getMock('\moss\http\request\RequestInterface');
        $requestPass
            ->expects($this->any())
            ->method('controller')
            ->will($this->returnValue('Bundle:something:login'));
        $this->assertFalse($area->match($requestPass));
    }

    public function testAuthUserRoleFail()
    {
        $area = new Area('Bundle:*:!login|logout', array('role'));

        $user = $this->getMock('\moss\security\UserInterface');
        $user
            ->expects($this->any())
            ->method('hasRole')
            ->will($this->returnValue(false));

        $this->assertFalse($area->authorize($user));
    }

    public function testAuthUserIPFail()
    {
        $area = new Area('Bundle:*:!login|logout', array(), array('127.0.0.1'));
        $user = $this->getMock('\moss\security\UserInterface');
        $this->assertFalse($area->authorize($user, '127.0.0.2'));
    }

    public function testAuthUserRole()
    {
        $area = new Area('Bundle:*:!login|logout', array('role'));

        $user = $this->getMock('\moss\security\UserInterface');
        $user
            ->expects($this->any())
            ->method('hasRole')
            ->will($this->returnValue(true));

        $this->assertTrue($area->authorize($user));
    }

    public function testAuthUserIP()
    {
        $area = new Area('Bundle:*:!login|logout', array(), array('127.0.0.1'));
        $user = $this->getMock('\moss\security\UserInterface');
        $this->assertTrue($area->authorize($user, '127.0.0.1'));
    }
}

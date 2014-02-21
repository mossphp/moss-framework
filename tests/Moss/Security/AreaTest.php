<?php
namespace Moss\Security;


class AreaTest extends \PHPUnit_Framework_TestCase
{

    public function testPattern()
    {
        $area = new Area('bundle:*:!login|logout');
        $this->assertEquals('bundle:*:!login|logout', $area->pattern());
    }

    public function testRoles()
    {
        $area = new Area('bundle:*:!login|logout', array('some', 'roles'));
        $this->assertEquals(array('some', 'roles'), $area->roles());
    }

    public function testIps()
    {
        $area = new Area('bundle:*:!login|!logout', array(), array('127.0.0.1'));
        $this->assertEquals(array('127.0.0.1'), $area->ips());
    }

    public function testMatch()
    {
        $area = new Area('bundle:*:!login|logout');

        $requestBlock = $this->getMock('\Moss\Http\request\RequestInterface');
        $requestBlock
            ->expects($this->any())
            ->method('controller')
            ->will($this->returnValue('bundle:something:index'));
        $this->assertTrue($area->match($requestBlock));

        $requestPass = $this->getMock('\Moss\Http\request\RequestInterface');
        $requestPass
            ->expects($this->any())
            ->method('controller')
            ->will($this->returnValue('bundle:something:login'));
        $this->assertFalse($area->match($requestPass));
    }

    public function testAuthUserRoleFail()
    {
        $area = new Area('bundle:*:!login|logout', array('role'));

        $user = $this->getMock('\Moss\Security\UserInterface');
        $user
            ->expects($this->any())
            ->method('hasRole')
            ->will($this->returnValue(false));

        $this->assertFalse($area->authorize($user));
    }

    public function testAuthUserIPFail()
    {
        $area = new Area('bundle:*:!login|logout', array(), array('127.0.0.1'));
        $user = $this->getMock('\Moss\Security\UserInterface');
        $this->assertFalse($area->authorize($user, '127.0.0.2'));
    }

    public function testAuthUserRole()
    {
        $area = new Area('bundle:*:!login|logout', array('role'));

        $user = $this->getMock('\Moss\Security\UserInterface');
        $user
            ->expects($this->any())
            ->method('hasRole')
            ->will($this->returnValue(true));

        $this->assertTrue($area->authorize($user));
    }

    public function testAuthUserIP()
    {
        $area = new Area('bundle:*:!login|logout', array(), array('127.0.0.1'));
        $user = $this->getMock('\Moss\Security\UserInterface');
        $this->assertTrue($area->authorize($user, '127.0.0.1'));
    }
}

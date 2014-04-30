<?php
namespace Moss\Security;


class AreaTest extends \PHPUnit_Framework_TestCase
{

    public function testPattern()
    {
        $area = new Area('bundle/*/(!login|logout)');
        $this->assertEquals('bundle/*/(!login|logout)', $area->pattern());
    }

    public function testRoles()
    {
        $area = new Area('bundle/*/(!login|logout)', array('some', 'roles'));
        $this->assertEquals(array('some', 'roles'), $area->roles());
    }

    public function testIps()
    {
        $area = new Area('bundle/*/(!login|!logout)', array(), array('127.0.0.1'));
        $this->assertEquals(array('127.0.0.1'), $area->ips());
    }

    /**
     * @dataProvider matchingProvider
     */
    public function testMatching($path)
    {
        $area = new Area('bundle/*/(!login|logout)');

        $request = $this->getMock('\Moss\Http\Request\RequestInterface');
        $request
            ->expects($this->any())
            ->method('path')
            ->will($this->returnValue($path));
        $this->assertTrue($area->match($request));
    }

    public function matchingProvider()
    {
        return array(
            array('/bundle/foo/notLogin'),
            array('/bundle/foo/notLogout'),
            array('/bundle/bar/yada'),
        );
    }

    /**
     * @dataProvider notMatchingProvider
     */
    public function testNotMatching($path)
    {
        $area = new Area('bundle/*/(!login|logout)');

        $request = $this->getMock('\Moss\Http\Request\RequestInterface');
        $request
            ->expects($this->any())
            ->method('path')
            ->will($this->returnValue($path));
        $this->assertFalse($area->match($request));
    }

    public function notMatchingProvider()
    {
        return array(
            array('/bundle/foo/login'),
            array('/bundle/bar/logout'),
            array('/bundle/foo/'),
            array('/bundle/bar/'),
            array('/foo/bar/yada'),
            array('/foo/bar/'),
        );
    }

    public function testAuthUserRoleFail()
    {
        $area = new Area('bundle/*/(!login|logout)', array('role'));

        $user = $this->getMock('\Moss\Security\UserInterface');
        $user
            ->expects($this->any())
            ->method('hasRole')
            ->will($this->returnValue(false));

        $this->assertFalse($area->authorize($user));
    }

    public function testAuthUserIPFail()
    {
        $area = new Area('bundle/*/(!login|logout)', array(), array('127.0.0.1'));
        $user = $this->getMock('\Moss\Security\UserInterface');
        $this->assertFalse($area->authorize($user, '127.0.0.2'));
    }

    public function testAuthUserRole()
    {
        $area = new Area('bundle/*/(!login|logout)', array('role'));

        $user = $this->getMock('\Moss\Security\UserInterface');
        $user
            ->expects($this->any())
            ->method('hasRole')
            ->will($this->returnValue(true));

        $this->assertTrue($area->authorize($user));
    }

    public function testAuthUserIP()
    {
        $area = new Area('bundle/*/(!login|logout)', array(), array('127.0.0.1'));
        $user = $this->getMock('\Moss\Security\UserInterface');
        $this->assertTrue($area->authorize($user, '127.0.0.1'));
    }
}

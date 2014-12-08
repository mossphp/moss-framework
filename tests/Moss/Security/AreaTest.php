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
        $area = new Area('bundle/*/(!login|logout)', ['some', 'roles']);
        $this->assertEquals(['some', 'roles'], $area->roles());
    }

    public function testIps()
    {
        $area = new Area('bundle/*/(!login|!logout)', [], ['127.0.0.1']);
        $this->assertEquals(['127.0.0.1'], $area->ips());
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
        return [
            ['/bundle/foo/yada'],
            ['/bundle/foo/notLogin'],
            ['/bundle/foo/notLogout'],
            ['/bundle/foo/loginNot'],
            ['/bundle/foo/logoutNot'],
            ['/bundle/foo/'],
            ['/bundle/foo']
        ];
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
        return [
            ['/bundle/foo/login'],
            ['/bundle/bar/logout'],
            ['/foo/bar/yada'],
            ['/foo/bar/']
        ];
    }

    public function testAuthUserRoleFail()
    {
        $area = new Area('bundle/*/(!login|logout)', ['role']);

        $user = $this->getMock('\Moss\Security\UserInterface');
        $user
            ->expects($this->any())
            ->method('hasRole')
            ->will($this->returnValue(false));

        $this->assertFalse($area->authorize($user));
    }

    public function testAuthUserIPFail()
    {
        $area = new Area('bundle/*/(!login|logout)', [], ['127.0.0.1']);
        $user = $this->getMock('\Moss\Security\UserInterface');
        $this->assertFalse($area->authorize($user, '127.0.0.2'));
    }

    public function testAuthUserRole()
    {
        $area = new Area('bundle/*/(!login|logout)', ['role']);

        $user = $this->getMock('\Moss\Security\UserInterface');
        $user
            ->expects($this->any())
            ->method('hasRole')
            ->will($this->returnValue(true));

        $this->assertTrue($area->authorize($user));
    }

    public function testAuthUserIP()
    {
        $area = new Area('bundle/*/(!login|logout)', [], ['127.0.0.1']);
        $user = $this->getMock('\Moss\Security\UserInterface');
        $this->assertTrue($area->authorize($user, '127.0.0.1'));
    }
}

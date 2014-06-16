<?php

/*
* This file is part of the moss-framework package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Moss\Http\Request;


class HeaderBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider headerProvider
     */
    public function testHeader($offset, $value, $expected)
    {
        $bag = new HeaderBag(array('HTTP_' . strtoupper($offset) => $value));

        $this->assertEquals($expected, $bag->get($offset));
    }

    public function headerProvider()
    {
        return array(
            array('content_length', 123456, 123456),
            array('content_md5', 'someMD5', 'someMD5'),
            array('content_type', 'text/plain', 'text/plain')
        );
    }

    public function testPHPAuth()
    {
        $bag = new HeaderBag(
            array(
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW' => 'pw'
            )
        );

        $this->assertEquals('user', $bag->get('php_auth_user'));
        $this->assertEquals('pw', $bag->get('php_auth_pw'));
    }

    public function testHTTPAuth()
    {
        $bag = new HeaderBag(
            array(
                'HTTP_AUTHORIZATION' => 'basic ' . base64_encode('user:pw')
            )
        );

        $this->assertEquals('user', $bag->get('php_auth_user'));
        $this->assertEquals('pw', $bag->get('php_auth_pw'));
    }

    public function testHTTPAuthRedirect()
    {
        $bag = new HeaderBag(
            array(
                'REDIRECT_HTTP_AUTHORIZATION' => 'basic ' . base64_encode('user:pw')
            )
        );

        $this->assertEquals('user', $bag->get('php_auth_user'));
        $this->assertEquals('pw', $bag->get('php_auth_pw'));
    }

    public function testLanguages()
    {
        $bag = new HeaderBag(
            array(
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,pl;q=0.6'
            )
        );

        $this->assertEquals(array('en', 'pl'), $bag->languages());
    }
}
 
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
        $bag = new HeaderBag(['HTTP_' . strtoupper($offset) => $value]);

        $this->assertEquals($expected, $bag->get($offset));
    }

    public function headerProvider()
    {
        return [
            ['content_length', 123456, 123456],
            ['content_md5', 'someMD5', 'someMD5'],
            ['content_type', 'text/plain', 'text/plain']
        ];
    }

    public function testPHPAuth()
    {
        $bag = new HeaderBag(
            [
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW' => 'pw'
            ]
        );

        $this->assertEquals('user', $bag->get('php_auth_user'));
        $this->assertEquals('pw', $bag->get('php_auth_pw'));
    }

    public function testHTTPAuth()
    {
        $bag = new HeaderBag(
            [
                'HTTP_AUTHORIZATION' => 'basic ' . base64_encode('user:pw')
            ]
        );

        $this->assertEquals('user', $bag->get('php_auth_user'));
        $this->assertEquals('pw', $bag->get('php_auth_pw'));
    }

    public function testHTTPAuthRedirect()
    {
        $bag = new HeaderBag(
            [
                'REDIRECT_HTTP_AUTHORIZATION' => 'basic ' . base64_encode('user:pw')
            ]
        );

        $this->assertEquals('user', $bag->get('php_auth_user'));
        $this->assertEquals('pw', $bag->get('php_auth_pw'));
    }

    public function testLanguages()
    {
        $bag = new HeaderBag(
            [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,pl;q=0.6'
            ]
        );

        $this->assertEquals(['en', 'pl'], $bag->languages());
    }

    public function testGetAll()
    {
        $bag = new HeaderBag(
            [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,pl;q=0.6',
                'CONTENT_LENGTH' => 3495
            ]
        );

        $expected = [
            'accept_language' => 'en-US,en;q=0.8,pl;q=0.6',
            'content_length' => 3495
        ];

        $this->assertEquals($expected, $bag->get());
    }

    /**
     * @dataProvider headerNameProvider
     */
    public function testCaseInsensitive($header)
    {
        $bag = new HeaderBag(
            [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,pl;q=0.6'
            ]
        );

        $this->assertEquals('en-US,en;q=0.8,pl;q=0.6', $bag->get($header));
    }

    public function headerNameProvider()
    {
        return [
            ['Accept-Language'],
            ['accept-language'],
            ['ACCEPT-LANGUAGE'],
            ['Accept_Language'],
            ['accept_language'],
            ['ACCEPT_LANGUAGE'],
        ];
    }
}

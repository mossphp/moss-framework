<?php
namespace Moss\Http\Request;

/**
 * @package Moss Test
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider serverProvider
     */
    public function testServer($offset, $value, $expected)
    {
        $request = new Request();
        $request->initialize(
            array(),
            array(),
            array(),
            array($offset => $value)
        );

        $this->assertEquals($expected, $request->server($offset));
    }

    public function serverProvider()
    {
        return array(
            array('REQUEST_METHOD', 'GET', 'GET'),
            array('REQUEST_METHOD', 'POST', 'POST'),
            array('REQUEST_METHOD', 'OPTIONS', 'OPTIONS'),
            array('REQUEST_METHOD', 'HEAD', 'HEAD'),
            array('REQUEST_METHOD', 'HEAD', 'HEAD'),
            array('REQUEST_METHOD', 'PUT', 'PUT'),
            array('REQUEST_METHOD', 'DELETE', 'DELETE'),
            array('REQUEST_METHOD', 'TRACE', 'TRACE'),

            array('SCRIPT_FILENAME', './foo.php', './foo.php'),
            array('DOCUMENT_ROOT', './', './'),

            array('HTTP_CONTENT_LENGTH', 123456, 123456),
            array('HTTP_CONTENT_MD5', 'someMD5', 'someMD5'),
            array('HTTP_CONTENT_TYPE', 'text/plain', 'text/plain'),
            array('HTTP_ACCEPT_LANGUAGE', 'en-US,en;q=0.8,pl;q=0.6', 'en-US,en;q=0.8,pl;q=0.6'),

            array('HTTP_X_REQUESTED_WITH', 'xmlhttprequest', 'xmlhttprequest'),

            array('HTTP_X_FORWARDED_PROTO', 'https', 'https'),
            array('HTTP_X_FORWARDED_PROTO', 'ssl', 'ssl'),
            array('HTTP_X_FORWARDED_PROTO', 'on', 'on'),
            array('HTTP_X_FORWARDED_PROTO', '1', '1'),
            array('HTTPS', 'on', 'on'),
            array('HTTPS', '1', '1'),

            array('REMOTE_ADDR', '127.0.0.1', '127.0.0.1'),
            array('HTTP_CLIENT_IP', '127.0.0.1', '127.0.0.1'),
            array('HTTP_X_FORWARDED_FOR', '127.0.0.1', '127.0.0.1'),

            array('HTTP_REFERER', 'http://foo.com', 'http://foo.com'),

            array('HTTP_AUTHORIZATION', 'basic dXNlcjpwdw==', 'basic dXNlcjpwdw=='),
            array('REDIRECT_HTTP_AUTHORIZATION', 'basic dXNlcjpwdw==', 'basic dXNlcjpwdw=='),
            array('PHP_AUTH_USER', 'user', 'user'),
            array('PHP_AUTH_PW', 'pw', 'pw'),
        );
    }

    /**
     * @dataProvider headerProvider
     */
    public function testHeader($offset, $value, $expected)
    {
        $request = new Request();
        $request->initialize(
            array(),
            array(),
            array(),
            array('HTTP_' . strtoupper($offset) => $value)
        );

        $this->assertEquals($expected, $request->header($offset));
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
        $request = new Request();
        $request->initialize(
            array(),
            array(),
            array(),
            array(
                'PHP_AUTH_USER' => 'user',
                'PHP_AUTH_PW' => 'pw'
            )
        );

        $this->assertEquals('user', $request->header('php_auth_user'));
        $this->assertEquals('pw', $request->header('php_auth_pw'));
    }

    public function testHTTPAuth()
    {
        $request = new Request();
        $request->initialize(
            array(),
            array(),
            array(),
            array(
                'HTTP_AUTHORIZATION' => 'basic ' . base64_encode('user:pw')
            )
        );

        $this->assertInstanceOf('\Moss\Http\request\RequestInterface', $request);
        $this->assertEquals('basic ' . base64_encode('user:pw'), $request->header('authorization'));
        $this->assertEquals('user', $request->header('php_auth_user'));
        $this->assertEquals('pw', $request->header('php_auth_pw'));
    }

    public function testHTTPAuthRedirect()
    {
        $request = new Request();
        $request->initialize(
            array(),
            array(),
            array(),
            array(
                'REDIRECT_HTTP_AUTHORIZATION' => 'basic ' . base64_encode('user:pw')
            )
        );
        $this->assertInstanceOf('\Moss\Http\request\RequestInterface', $request);
        $this->assertEquals('basic ' . base64_encode('user:pw'), $request->server('REDIRECT_HTTP_AUTHORIZATION'));
        $this->assertEquals('user', $request->header('php_auth_user'));
        $this->assertEquals('pw', $request->header('php_auth_pw'));
    }

    public function testLanguages()
    {
        $request = new Request();
        $request->initialize(
            array(),
            array(),
            array(),
            array(
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,pl;q=0.6'
            )
        );

        $this->assertEquals(array('en', 'pl'), $request->language());
    }

    public function testLocale()
    {
        $request = new Request();
        $request->initialize(
            array(),
            array(),
            array(),
            array(
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,pl;q=0.6'
            )
        );

        $this->assertEquals('en', $request->locale());
    }

    public function testQueryCLI()
    {
        $this->markTestIncomplete();
    }

    /**
     * @dataProvider queryProvider
     */
    public function testQuery($offset, $value, $expected)
    {
        $request = new Request();
        $request->initialize(
            array($offset => $value),
            array(),
            array(),
            array(
                'REQUEST_METHOD' => 'GET'
            )
        );

        $this->assertInstanceOf('\Moss\Http\Bag\BagInterface', $request->query);
        $this->assertInstanceOf('\Moss\Http\Bag\BagInterface', $request->query());
        $this->assertEquals($expected, $request->query->all());
    }

    public function queryProvider()
    {
        return array(
            array('foo', 'bar', array('foo' => 'bar')),
            array('controller', '\Foo\Bar::yada', array('controller' => '\Foo\Bar::yada')),
            array('locale', 'pl', array('locale' => 'pl')),
            array('format', 'json', array('format' => 'json')),
            array('foo.bar', 'yada', array('foo' => array('bar' => 'yada'))),
            array('f.o.o.b.a.r', 'deep', array('f' => array('o' => array('o' => array('b' => array('a' => array('r' => 'deep'))))))),
        );
    }

    /**
     * @dataProvider bodyProvider
     */
    public function testBody($offset, $value, $expected)
    {
        $request = new Request();
        $request->initialize(
            array(),
            array($offset => $value),
            array(),
            array(
                'REQUEST_METHOD' => 'POST'
            )
        );

        $this->assertInstanceOf('\Moss\Http\Bag\BagInterface', $request->body);
        $this->assertInstanceOf('\Moss\Http\Bag\BagInterface', $request->body());
        $this->assertEquals($expected, $request->body->all());
    }

    public function bodyProvider()
    {
        return array(
            array('foo', 'bar', array('foo' => 'bar')),
            array('locale', 'pl', array('locale' => 'pl')),
            array('format', 'json', array('format' => 'json')),
            array('foo.bar', 'yada', array('foo' => array('bar' => 'yada'))),
            array('f.o.o.b.a.r', 'deep', array('f' => array('o' => array('o' => array('b' => array('a' => array('r' => 'deep'))))))),
        );
    }

    public function testSession()
    {
        $request = new Request(
            $this->getMock('\Moss\Http\Session\SessionInterface'),
            $this->getMock('\Moss\Http\Cookie\CookieInterface')
        );

        $this->assertInstanceOf('\Moss\Http\Session\SessionInterface', $request->session);
        $this->assertInstanceOf('\Moss\Http\Session\SessionInterface', $request->session());
    }

    public function testCookie()
    {
        $request = new Request(
            $this->getMock('\Moss\Http\Session\SessionInterface'),
            $this->getMock('\Moss\Http\Cookie\CookieInterface')
        );

        $this->assertInstanceOf('\Moss\Http\Cookie\CookieInterface', $request->cookie);
        $this->assertInstanceOf('\Moss\Http\Cookie\CookieInterface', $request->cookie());
    }

    public function testFiles()
    {
        $request = new Request();
        $request->initialize();

        $this->assertInstanceOf('\Moss\Http\Request\FilesBag', $request->files);
        $this->assertInstanceOf('\Moss\Http\Request\FilesBag', $request->files());
    }

    public function testIsAjax()
    {
        $request = new Request();
        $request->initialize(
            array(),
            array(),
            array(),
            array('HTTP_X_REQUESTED_WITH' => 'xmlhttprequest')
        );

        $this->assertTrue($request->isAjax());
    }

    /**
     * @dataProvider secureProvider
     */
    public function testIsSecure($server)
    {
        $request = new Request();
        $request->initialize(
            array(),
            array(),
            array(),
            $server
        );

        $this->assertTrue($request->isSecure());
    }

    public function secureProvider()
    {
        return array(
            array(array('HTTP_X_FORWARDED_PROTO' => 'https')),
            array(array('HTTP_X_FORWARDED_PROTO' => 'ssl')),
            array(array('HTTP_X_FORWARDED_PROTO' => 'on')),
            array(array('HTTP_X_FORWARDED_PROTO' => '1')),
            array(array('HTTPS' => 'on')),
            array(array('HTTPS' => '1')),
        );
    }

    /**
     * @dataProvider methodProvider
     */
    public function testMethod($method)
    {
        $request = new Request();
        $request->initialize(
            array(),
            array(),
            array(),
            array('REQUEST_METHOD' => $method)
        );

        $this->assertEquals($method, $request->method());
    }

    public function methodProvider()
    {
        return array(
            array('GET'),
            array('POST'),
            array('OPTIONS'),
            array('HEAD'),
            array('HEAD'),
            array('PUT'),
            array('DELETE'),
            array('TRACE'),
        );
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testSchema($server, $expected)
    {
        $request = new Request();
        $request->initialize(
            array(),
            array(),
            array(),
            $server
        );

        $this->assertEquals($expected, $request->schema());
    }

    public function schemaProvider()
    {
        return array(
            array(array('HTTP_X_FORWARDED_PROTO' => 'on'), 'https'),
            array(array('HTTP_X_FORWARDED_PROTO' => '1'), 'https'),
            array(array('HTTPS' => 'on'), 'https'),
            array(array('HTTPS' => '1'), 'https'),
            array(array(), 'http'),
        );
    }

    public function testPathsWithProperDomainRedirect()
    {
        $request = new Request();
        $request->initialize(
            array(
                'foo' => 'bar'
            ),
            array(),
            array(),
            array(
                'REQUEST_METHOD' => 'GET',
                'SERVER_PROTOCOL' => 'HTTP/1.1',
                'REQUEST_URI' => '/foo/index.html',
                'DOCUMENT_ROOT' => '/home/foo/www/',
                'SCRIPT_FILENAME' => '/home/foo/www/web/index.php',
                'HTTP_HOST' => 'test.com',
                'REDIRECT_URL' => '/',
            )
        );

        $this->assertEquals('http', $request->schema());
        $this->assertEquals('test.com', $request->host());
        $this->assertEquals('/', $request->dir());
        $this->assertEquals('/foo/index.html', $request->path());
        $this->assertEquals('http://test.com/foo/index.html', $request->uri(false));
        $this->assertEquals('http://test.com/foo/index.html?foo=bar', $request->uri(true));
    }

    public function testPathsWithInvalidDomainRedirect()
    {
        $request = new Request();
        $request->initialize(
            array(
                'foo' => 'bar'
            ),
            array(),
            array(),
            array(
                'REQUEST_METHOD' => 'GET',
                'SERVER_PROTOCOL' => 'HTTP/1.1',
                'REQUEST_URI' => '/foo/index.html',
                'DOCUMENT_ROOT' => '/home/foo/www/',
                'SCRIPT_FILENAME' => '/home/foo/www/web/index.php',
                'HTTP_HOST' => 'test.com',
                'REDIRECT_URL' => '/web/',
            )
        );

        $this->assertEquals('http://test.com/web/', $request->baseName());
        $this->assertEquals('http', $request->schema());
        $this->assertEquals('test.com', $request->host());
        $this->assertEquals('/web/', $request->dir());
        $this->assertEquals('/foo/index.html', $request->path());
        $this->assertEquals('http://test.com/web/foo/index.html', $request->uri(false));
        $this->assertEquals('http://test.com/web/foo/index.html?foo=bar', $request->uri(true));
    }

    /**
     * @dataProvider ipProvider
     */
    public function testIp($header)
    {
        $request = new Request();
        $request->initialize(
            array(),
            array(),
            array(),
            array($header => '127.0.0.1')
        );

        $this->assertEquals('127.0.0.1', $request->clientIp());
    }

    public function ipProvider()
    {
        return array(
            array('REMOTE_ADDR'),
            array('HTTP_CLIENT_IP'),
            array('HTTP_X_FORWARDED_FOR')
        );
    }

    public function testController()
    {
        $request = new Request();
        $request->initialize(
            array('controller' => '\Foo\Bar\Controller::yada'),
            array(),
            array(),
            array()
        );

        $this->assertEquals('\Foo\Bar\Controller::yada', $request->controller());
    }

    public function testFormat()
    {
        $request = new Request();
        $request->initialize(
            array('format' => 'json'),
            array(),
            array(),
            array()
        );

        $this->assertEquals('json', $request->format());
    }

    public function testReferrer()
    {
        $request = new Request();
        $request->initialize(
            array(),
            array(),
            array(),
            array('HTTP_REFERER' => 'http://www.foo.bar/')
        );

        $this->assertEquals('http://www.foo.bar/', $request->referrer());
    }
}
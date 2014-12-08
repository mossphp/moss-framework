<?php
namespace Moss\Http\Request;

/**
 * @package Moss Test
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        if (isset($GLOBALS['argc'])) {
            unset($GLOBALS['argc']);
        }

        if (isset($GLOBALS['argv'])) {
            unset($GLOBALS['argv']);
        }
    }

    /**
     * @dataProvider serverProvider
     */
    public function testServer($offset, $value, $expected)
    {
        $request = new Request();
        $request->initialize(
            [],
            [],
            [],
            [$offset => $value]
        );

        $this->assertEquals($expected, $request->server($offset));
    }

    public function serverProvider()
    {
        return [
            ['REQUEST_METHOD', 'GET', 'GET'],
            ['REQUEST_METHOD', 'POST', 'POST'],
            ['REQUEST_METHOD', 'OPTIONS', 'OPTIONS'],
            ['REQUEST_METHOD', 'HEAD', 'HEAD'],
            ['REQUEST_METHOD', 'HEAD', 'HEAD'],
            ['REQUEST_METHOD', 'PUT', 'PUT'],
            ['REQUEST_METHOD', 'DELETE', 'DELETE'],
            ['REQUEST_METHOD', 'TRACE', 'TRACE'],

            ['SCRIPT_FILENAME', './foo.php', './foo.php'],
            ['DOCUMENT_ROOT', './', './'],

            ['HTTP_CONTENT_LENGTH', 123456, 123456],
            ['HTTP_CONTENT_MD5', 'someMD5', 'someMD5'],
            ['HTTP_CONTENT_TYPE', 'text/plain', 'text/plain'],
            ['HTTP_ACCEPT_LANGUAGE', 'en-US,en;q=0.8,pl;q=0.6', 'en-US,en;q=0.8,pl;q=0.6'],

            ['HTTP_X_REQUESTED_WITH', 'xmlhttprequest', 'xmlhttprequest'],

            ['HTTP_X_FORWARDED_PROTO', 'https', 'https'],
            ['HTTP_X_FORWARDED_PROTO', 'ssl', 'ssl'],
            ['HTTP_X_FORWARDED_PROTO', 'on', 'on'],
            ['HTTP_X_FORWARDED_PROTO', '1', '1'],
            ['HTTPS', 'on', 'on'],
            ['HTTPS', '1', '1'],

            ['REMOTE_ADDR', '127.0.0.1', '127.0.0.1'],
            ['HTTP_CLIENT_IP', '127.0.0.1', '127.0.0.1'],
            ['HTTP_X_FORWARDED_FOR', '127.0.0.1', '127.0.0.1'],

            ['HTTP_REFERER', 'http://foo.com', 'http://foo.com'],

            ['HTTP_AUTHORIZATION', 'basic dXNlcjpwdw==', 'basic dXNlcjpwdw=='],
            ['REDIRECT_HTTP_AUTHORIZATION', 'basic dXNlcjpwdw==', 'basic dXNlcjpwdw=='],
            ['PHP_AUTH_USER', 'user', 'user'],
            ['PHP_AUTH_PW', 'pw', 'pw'],
        ];
    }


    public function testLocale()
    {
        $request = new Request();
        $request->initialize(
            [],
            [],
            [],
            [
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,pl;q=0.6'
            ]
        );

        $this->assertEquals('en', $request->locale());
    }

    /**
     * @dataProvider consoleProvider
     */
    public function testConsole($arg, $expected, $url = null)
    {
        $GLOBALS['argc'] = count($arg);
        $GLOBALS['argv'] = $arg;

        $request = new Request();
        $request->initialize(
            [],
            [],
            [],
            [
                'REQUEST_METHOD' => 'CLI'
            ]
        );

        $this->assertEquals($expected, $request->query()->all());
        $this->assertEquals($url, $request->path());
    }

    public function consoleProvider()
    {
        return [
            [
                ['index.php', 'foo'],
                [],
                'foo'
            ],
            [
                ['index.php', '-foo'],
                ['foo' => true]
            ],
            [
                ['index.php', '--foo'],
                ['foo' => true]
            ],
            [
                ['index.php', 'foo=bar'],
                [],
                'foo=bar'
            ],
            [
                ['index.php', '-foo=bar'],
                ['foo' => 'bar']
            ],
            [
                ['index.php', '--foo=bar'],
                ['foo' => 'bar']
            ],
        ];
    }

    /**
     * @dataProvider queryProvider
     */
    public function testQuery($offset, $value, $expected)
    {
        $request = new Request();
        $request->initialize(
            [$offset => $value],
            [],
            [],
            [
                'REQUEST_METHOD' => 'GET'
            ]
        );

        $this->assertInstanceOf('\Moss\Bag\BagInterface', $request->query());
        $this->assertEquals($expected, $request->query()->all());
    }

    public function queryProvider()
    {
        return [
            ['foo', 'bar', ['foo' => 'bar']],
            ['controller', '\Foo\Bar::yada', ['controller' => '\Foo\Bar::yada']],
            ['locale', 'pl', ['locale' => 'pl']],
            ['format', 'json', ['format' => 'json']],
            ['foo.bar', 'yada', ['foo' => ['bar' => 'yada']]],
            ['f.o.o.b.a.r', 'deep', ['f' => ['o' => ['o' => ['b' => ['a' => ['r' => 'deep']]]]]]],
        ];
    }

    /**
     * @dataProvider bodyProvider
     */
    public function testBody($offset, $value, $expected)
    {
        $request = new Request();
        $request->initialize(
            [],
            [$offset => $value],
            [],
            [
                'REQUEST_METHOD' => 'POST'
            ]
        );

        $this->assertInstanceOf('\Moss\Bag\BagInterface', $request->body());
        $this->assertEquals($expected, $request->body()->all());
    }

    public function bodyProvider()
    {
        return [
            ['foo', 'bar', ['foo' => 'bar']],
            ['locale', 'pl', ['locale' => 'pl']],
            ['format', 'json', ['format' => 'json']],
            ['foo.bar', 'yada', ['foo' => ['bar' => 'yada']]],
            ['f.o.o.b.a.r', 'deep', ['f' => ['o' => ['o' => ['b' => ['a' => ['r' => 'deep']]]]]]],
        ];
    }

    public function testSession()
    {
        $request = new Request(
            $this->getMock('\Moss\Http\Session\SessionInterface'),
            $this->getMock('\Moss\Http\Cookie\CookieInterface')
        );

        $this->assertInstanceOf('\Moss\Http\Session\SessionInterface', $request->session());
    }

    public function testCookie()
    {
        $request = new Request(
            $this->getMock('\Moss\Http\Session\SessionInterface'),
            $this->getMock('\Moss\Http\Cookie\CookieInterface')
        );

        $this->assertInstanceOf('\Moss\Http\Cookie\CookieInterface', $request->cookie());
    }

    public function testFiles()
    {
        $request = new Request();
        $request->initialize();

        $this->assertInstanceOf('\Moss\Http\Request\FilesBag', $request->files());
    }

    public function testIsAjax()
    {
        $request = new Request();
        $request->initialize(
            [],
            [],
            [],
            ['HTTP_X_REQUESTED_WITH' => 'xmlhttprequest']
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
            [],
            [],
            [],
            $server
        );

        $this->assertTrue($request->isSecure());
    }

    public function secureProvider()
    {
        return [
            [['HTTP_X_FORWARDED_PROTO' => 'https']],
            [['HTTP_X_FORWARDED_PROTO' => 'ssl']],
            [['HTTP_X_FORWARDED_PROTO' => 'on']],
            [['HTTP_X_FORWARDED_PROTO' => '1']],
            [['HTTPS' => 'on']],
            [['HTTPS' => '1']],
        ];
    }

    /**
     * @dataProvider methodProvider
     */
    public function testMethod($method)
    {
        $request = new Request();
        $request->initialize(
            [],
            [],
            [],
            ['REQUEST_METHOD' => $method]
        );

        $this->assertEquals($method, $request->method());
    }

    public function methodProvider()
    {
        return [
            ['GET'],
            ['POST'],
            ['OPTIONS'],
            ['HEAD'],
            ['HEAD'],
            ['PUT'],
            ['DELETE'],
            ['TRACE'],
        ];
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testSchema($server, $expected)
    {
        $request = new Request();
        $request->initialize(
            [],
            [],
            [],
            $server
        );

        $this->assertEquals($expected, $request->schema());
    }

    public function schemaProvider()
    {
        return [
            [['HTTP_X_FORWARDED_PROTO' => 'on'], 'https'],
            [['HTTP_X_FORWARDED_PROTO' => '1'], 'https'],
            [['HTTPS' => 'on'], 'https'],
            [['HTTPS' => '1'], 'https'],
            [[], 'http'],
        ];
    }

    public function testBaseName()
    {
        $request = new Request();
        $this->assertEquals('http://foo.test.com/bar/yada/', $request->baseName('http://foo.test.com/bar/yada'));
    }

    public function testPathsWithQueryString()
    {
        $request = new Request();
        $request->initialize(
            [
                'foo' => 'bar'
            ],
            [],
            [],
            [
                'REQUEST_METHOD' => 'GET',
                'SERVER_PROTOCOL' => 'HTTP/1.1',
                'REQUEST_URI' => '/foo/index.html?foo=bar',
                'DOCUMENT_ROOT' => '/home/foo/www/',
                'SCRIPT_FILENAME' => '/home/foo/www/web/index.php',
                'HTTP_HOST' => 'test.com',
                'REDIRECT_URL' => '/',
            ]
        );

        $this->assertEquals('http://test.com/', $request->baseName());
        $this->assertEquals('http', $request->schema());
        $this->assertEquals('test.com', $request->host());
        $this->assertEquals('/', $request->dir());
        $this->assertEquals('/foo/index.html', $request->path());
        $this->assertEquals('http://test.com/foo/index.html', $request->uri(false));
        $this->assertEquals('http://test.com/foo/index.html?foo=bar', $request->uri(true));
    }

    public function testPathsWithProperDomainRedirect()
    {
        $request = new Request();
        $request->initialize(
            [
                'foo' => 'bar'
            ],
            [],
            [],
            [
                'REQUEST_METHOD' => 'GET',
                'SERVER_PROTOCOL' => 'HTTP/1.1',
                'REQUEST_URI' => '/foo/index.html',
                'DOCUMENT_ROOT' => '/home/foo/www/',
                'SCRIPT_FILENAME' => '/home/foo/www/web/index.php',
                'HTTP_HOST' => 'test.com',
                'REDIRECT_URL' => '/',
            ]
        );

        $this->assertEquals('http://test.com/', $request->baseName());
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
            [
                'foo' => 'bar'
            ],
            [],
            [],
            [
                'REQUEST_METHOD' => 'GET',
                'SERVER_PROTOCOL' => 'HTTP/1.1',
                'REQUEST_URI' => '/foo/index.html',
                'DOCUMENT_ROOT' => '/home/foo/www/',
                'SCRIPT_FILENAME' => '/home/foo/www/web/index.php',
                'HTTP_HOST' => 'test.com',
                'REDIRECT_URL' => '/web/',
            ]
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
            [],
            [],
            [],
            [$header => '127.0.0.1']
        );

        $this->assertEquals('127.0.0.1', $request->clientIp());
    }

    public function ipProvider()
    {
        return [
            ['REMOTE_ADDR'],
            ['HTTP_CLIENT_IP'],
            ['HTTP_X_FORWARDED_FOR']
        ];
    }

    public function testRoute()
    {
        $request = new Request();
        $request->route('route_name');

        $this->assertEquals('route_name', $request->route());
    }

    public function testFormat()
    {
        $request = new Request();
        $request->initialize(
            ['format' => 'json'],
            [],
            [],
            []
        );

        $this->assertEquals('json', $request->format());
    }

    public function testReferrer()
    {
        $request = new Request();
        $request->initialize(
            [],
            [],
            [],
            ['HTTP_REFERER' => 'http://www.foo.bar/']
        );

        $this->assertEquals('http://www.foo.bar/', $request->referrer());
    }
}

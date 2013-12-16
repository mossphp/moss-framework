<?php
namespace moss\http\request;

use moss\http\cookie\Cookie;
use moss\http\session\Session;

/**
 * @package Moss Test
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $Request = new Request();
        $this->assertInstanceOf('\moss\http\request\RequestInterface', $Request);
    }

    public function testConstructContent()
    {
        $_SERVER['CONTENT_LENGTH'] = 123456;
        $_SERVER['CONTENT_MD5'] = 'someMD5';
        $_SERVER['CONTENT_TYPE'] = 'text/plan';

        $Request = new Request();
        $this->assertInstanceOf('\moss\http\request\RequestInterface', $Request);
        $this->assertEquals($_SERVER['CONTENT_LENGTH'], $Request->server('CONTENT_LENGTH'));
        $this->assertEquals($_SERVER['CONTENT_MD5'], $Request->server('CONTENT_MD5'));
        $this->assertEquals($_SERVER['CONTENT_TYPE'], $Request->server('CONTENT_TYPE'));
        $this->assertEquals($_SERVER['CONTENT_LENGTH'], $Request->header('content_length'));
        $this->assertEquals($_SERVER['CONTENT_MD5'], $Request->header('content_md5'));
        $this->assertEquals($_SERVER['CONTENT_TYPE'], $Request->header('content_type'));
    }

    public function testConstructWithMagicQuotes()
    {
        if (version_compare(phpversion(), '6.0.0-dev', '<') && get_magic_quotes_gpc()) {
            $this->markTestSkipped('Magic quotes are off');
        }
    }

    public function testSession()
    {
        $_SESSION['foo'] = 'foo';

        $Request = new Request(new Session());
        $this->assertEquals('foo', $Request->session()->get('foo'));
    }

    public function testSessionDeep()
    {
        $_SESSION['foo'] = array('bar' => 'bar');

        $Request = new Request(new Session());
        $this->assertEquals('bar', $Request->session()->get('foo.bar'));
    }

    public function testCookie()
    {
        $_COOKIE['foo'] = 'foo';

        $Request = new Request(null, new Cookie());
        $this->assertEquals('foo', $Request->cookie()->get('foo'));
    }

    public function testCookieDeep()
    {
        $_COOKIE['foo'] = array('bar' => 'bar');

        $Request = new Request(null, new Cookie());
        $this->assertEquals('bar', $Request->cookie()->get('foo.bar'));
    }

    public function getServerBlank()
    {
        $Request = new Request();
        $this->assertNull($Request->server('foobar'));
    }

    public function testHeaderBlank()
    {
        $Request = new Request();
        $this->assertNull($Request->header('foobar'));
    }

    public function testQuery()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = array(
            'foo' => 'bar',
            'controller' => 'foobar',
            'locale' => 'pl',
            'format' => 'json'
        );

        $Request = new Request();

        $this->assertEquals('bar', $Request->query()->get('foo'));
        $this->assertEquals('foobar', $Request->query()->get('controller'));
        $this->assertEquals('pl', $Request->query()->get('locale'));
        $this->assertEquals('json', $Request->query()->get('format'));
    }

    public function testSetQuery()
    {
        $Request = new Request();

        $this->assertEquals('bar', $Request->query()->get('foo', 'bar'));
        $this->assertEquals('foobar', $Request->query()->get('controller', 'foobar'));
        $this->assertEquals('pl', $Request->query()->get('locale', 'pl'));
        $this->assertEquals('json', $Request->query()->get('format', 'json'));
        $this->assertEquals('yada', $Request->query()->get('foo.bar', 'yada'));
        $this->assertEquals('deep', $Request->query()->get('f.o.o.b.a.r', 'deep'));
    }

    public function testPost()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(
            'foo' => 'bar',
            'controller' => 'foobar',
            'locale' => 'pl',
            'format' => 'json'
        );

        $Request = new Request();
        $this->assertEquals('bar', $Request->post()->get('foo'));
        $this->assertEquals('foobar', $Request->post()->get('controller'));
        $this->assertEquals('pl', $Request->post()->get('locale'));
        $this->assertEquals('json', $Request->post()->get('format'));
    }

    public function testSetPost()
    {
        $Request = new Request();
        $this->assertEquals('bar', $Request->post()->get('foo', 'bar'));
        $this->assertEquals('foobar', $Request->post()->get('controller', 'foobar'));
        $this->assertEquals('pl', $Request->post()->get('locale', 'pl'));
        $this->assertEquals('json', $Request->post()->get('format', 'json'));
        $this->assertEquals('yada', $Request->post()->get('foo.bar.zope', 'yada'));
        $this->assertEquals('deep', $Request->post()->get('f.o.o.b.a.r', 'deep'));
    }

    public function testFile()
    {
        $_FILES['foo'] = array(
            'name' => 'bar.txt',
            'type' => 'text/plain',
            'tmp_name' => 'whatever2',
            'error' => 0,
            'size' => 123
        );
        $result = array(
            'name' => 'bar.txt',
            'type' => 'text/plain',
            'tmp_name' => 'whatever2',
            'error' => 0,
            'error_text' => null,
            'size' => 123
        );

        $Request = new Request();
        $this->assertEquals($result, $Request->files()->get('foo'));
    }

    public function testFileDeep()
    {
        $_FILES = array(
            'foo' => array(
                'name' => array(
                    'foo' => 'foo.txt',
                    'bar' => 'bar.txt'
                ),
                'type' => array(
                    'foo' => 'text/plain',
                    'bar' => 'text/plain'
                ),
                'tmp_name' => array(
                    'foo' => 'foo_tmp',
                    'bar' => 'bar_tmp'
                ),
                'error' => array(
                    'foo' => 0,
                    'bar' => 0
                ),
                'size' => array(
                    'foo' => 123,
                    'bar' => 456
                )
            ),
        );

        $result = array(
            'foo' => array(
                'name' => 'foo.txt',
                'type' => 'text/plain',
                'tmp_name' => 'foo_tmp',
                'error' => 0,
                'error_text' => null,
                'size' => 123
            ),
            'bar' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'bar_tmp',
                'error' => 0,
                'error_text' => null,
                'size' => 456
            )
        );

        $Request = new Request();
        $this->assertEquals($result, $Request->files()->get('foo'));
    }

    // todo - refactor to data provider
    public function testFileError()
    {
        $_FILES = array(
            'bar1' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 1,
                'size' => 0
            ),
            'bar2' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 2,
                'size' => 0
            ),
            'bar3' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 3,
                'size' => 0
            ),
            'bar4' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 4,
                'size' => 0
            ),
            'bar5' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 5,
                'size' => 0
            ),
            'bar6' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 6,
                'size' => 0
            ),
            'bar7' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 7,
                'size' => 0
            ),
            'bar8' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 8,
                'size' => 0
            )
        );

        $result = array(
            'bar1' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 1,
                'error_text' => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                'size' => 0
            ),
            'bar2' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 2,
                'error_text' => 'The uploaded file exceeds the MAX_FILE_SIZE directive specified in HTML form.',
                'size' => 0
            ),
            'bar3' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 3,
                'error_text' => 'The uploaded file was only partially uploaded.',
                'size' => 0
            ),
            'bar4' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 4,
                'error_text' => 'No file was uploaded.',
                'size' => 0
            ),
            'bar5' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 5,
                'error_text' => 'Unknown error occurred.',
                'size' => 0
            ),
            'bar6' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 6,
                'error_text' => 'Missing a temporary folder.',
                'size' => 0
            ),
            'bar7' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 7,
                'error_text' => 'Failed to write file to disk.',
                'size' => 0
            ),
            'bar8' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 8,
                'error_text' => 'A PHP extension stopped the file upload.',
                'size' => 0
            )
        );

        $Request = new Request();
        $this->assertEquals($result['bar1'], $Request->files()->get('bar1'));
        $this->assertEquals($result['bar2'], $Request->files()->get('bar2'));
        $this->assertEquals($result['bar3'], $Request->files()->get('bar3'));
        $this->assertEquals($result['bar4'], $Request->files()->get('bar4'));
        $this->assertEquals($result['bar5'], $Request->files()->get('bar5'));
        $this->assertEquals($result['bar6'], $Request->files()->get('bar6'));
        $this->assertEquals($result['bar7'], $Request->files()->get('bar7'));
        $this->assertEquals($result['bar8'], $Request->files()->get('bar8'));
    }

    public function testIsSecureFalse()
    {
        $Request = new Request();
        $this->assertFalse($Request->isSecure());
    }

    public function testIsSecureTrue()
    {
        $_SERVER['HTTPS'] = 'ON';
        $Request = new Request();
        $this->assertTrue($Request->isSecure());
    }

    public function testIsAjaxFalse()
    {
        $Request = new Request();
        $this->assertFalse($Request->isAjax());
    }

    public function testIsAjaxTrue()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHTTPREQUEST';
        $Request = new Request();
        $this->assertTrue($Request->isAjax());
    }

    public function testMethodCLI()
    {
        $_SERVER['REQUEST_METHOD'] = null;
        $Request = new Request();
        $this->assertEquals('CLI', $Request->method());
    }

    public function testMethodGET()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $Request = new Request();
        $this->assertEquals('GET', $Request->method());
    }

    public function testMethodPOST()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $Request = new Request();
        $this->assertEquals('POST', $Request->method());
    }

    public function testMethodPUT()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $Request = new Request();
        $this->assertEquals('PUT', $Request->method());
    }

    public function testMethodDELETE()
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $Request = new Request();
        $this->assertEquals('DELETE', $Request->method());
    }

    public function testSchema()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'http';
        $Request = new Request();
        $this->assertEquals('http', $Request->schema());
    }

    public function testSchemaSecure()
    {
        $_SERVER['HTTPS'] = 'on';
        $Request = new Request();
        $this->assertEquals('https', $Request->schema());
    }

    public function testDomain()
    {
        $_SERVER['HTTP_HOST'] = 'foo.test.com';
        $Request = new Request();
        $this->assertEquals('foo.test.com', $Request->host());
    }

    public function testDir()
    {
        $_SERVER['PHP_SELF'] = '/web/';
        $_SERVER['HTTP_HOST'] = 'test.com';

        $Request = new Request();
        $this->assertEquals('/web/', $Request->dir());
    }

    public function testBaseName()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
        $_SERVER['REQUEST_URI'] = '/foo/index.html?foo=bar';
        $_SERVER['PHP_SELF'] = '/test';
        $_SERVER['HTTP_HOST'] = 'test.com';

        $Request = new Request();
        $this->assertEquals('http://test.com/', $Request->baseName());
    }

    public function testSetBaseName()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
        $_SERVER['REQUEST_URI'] = '/foo/index.html?foo=bar';
        $_SERVER['PHP_SELF'] = '/test';
        $_SERVER['HTTP_HOST'] = 'test.com';

        $Request = new Request();
        $this->assertEquals('http://test.com/', $Request->baseName());

        $Request->baseName('http://yada.com/');
        $this->assertEquals('http://yada.com/', $Request->baseName());
    }

    public function testClientIpRemote()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $Request = new Request();
        $this->assertEquals('127.0.0.1', $Request->clientIp());
    }

    public function testClientIpForwarded()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';

        $Request = new Request();
        $this->assertEquals('127.0.0.1', $Request->clientIp());
    }

    public function testClientIpHTTPClientIp()
    {
        $_SERVER['HTTP_CLIENT_IP'] = '127.0.0.1';

        $Request = new Request();
        $this->assertEquals('127.0.0.1', $Request->clientIp());
    }

    public function testController()
    {
        $Request = new Request();
        $this->assertEquals(null, $Request->controller());
    }

    public function testSetController()
    {
        $Request = new Request();
        $this->assertEquals(null, $Request->controller());
        $this->assertEquals('foobar', $Request->controller('foobar'));
    }

    public function testURI()
    {
        $_SERVER['REQUEST_URI'] = '/foo/index.html?foo=bar';

        $Request = new Request();
        $this->assertEquals('/foo/index.html', $Request->url());
    }

    public function testEmptyInvalidRedirect()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
        $_SERVER['REQUEST_URI'] = '/web/foo/index.html?foo=bar';
        $_SERVER['PHP_SELF'] = '/web';
        $_SERVER['HTTP_HOST'] = 'test.com';
        $_SERVER['REDIRECT_URL'] = null;

        $Request = new Request();
        $this->assertEquals('/', $Request->dir());
        $this->assertEquals('/web/foo/index.html', $Request->url());
    }

    public function testInvalidRedirect()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
        $_SERVER['REQUEST_URI'] = '/web/foo/index.html?foo=bar';
        $_SERVER['PHP_SELF'] = '/web/';
        $_SERVER['HTTP_HOST'] = 'test.com';
        $_SERVER['REDIRECT_URL'] = '/';

        $Request = new Request();
        $this->assertEquals('/', $Request->dir());
        $this->assertEquals('/foo/index.html', $Request->url());
    }

    public function testReferer()
    {
        $_SERVER['HTTP_REFERER'] = 'test.com';

        $Request = new Request();
        $this->assertEquals('test.com', $Request->referrer());
    }

    public function testLocaleFromHeader()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'pl,en-us;q=0.7,en;q=0.3';

        $Request = new Request();
        $this->assertEquals('pl', $Request->locale());
    }

    public function testLocaleFromQuery()
    {
        $_GET['locale'] = 'pl';

        $Request = new Request();
        $this->assertEquals('pl', $Request->locale());
    }

    public function testSetLocale()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'pl,en-us;q=0.7,en;q=0.3';

        $Request = new Request();
        $Request->locale('en');
        $this->assertEquals('en', $Request->locale());
    }

    public function testFormat()
    {
        $_GET['format'] = 'json';

        $Request = new Request();
        $this->assertEquals('json', $Request->format());
    }

    public function testSetFormat()
    {
        $Request = new Request();
        $Request->format('json');
        $this->assertEquals('json', $Request->format());
    }
}
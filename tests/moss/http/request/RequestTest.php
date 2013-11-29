<?php
namespace moss\http\request;

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
        $this->assertEquals($_SERVER['CONTENT_LENGTH'], $Request->getServer('CONTENT_LENGTH'));
        $this->assertEquals($_SERVER['CONTENT_MD5'], $Request->getServer('CONTENT_MD5'));
        $this->assertEquals($_SERVER['CONTENT_TYPE'], $Request->getServer('CONTENT_TYPE'));
        $this->assertEquals($_SERVER['CONTENT_LENGTH'], $Request->getHeader('content_length'));
        $this->assertEquals($_SERVER['CONTENT_MD5'], $Request->getHeader('content_md5'));
        $this->assertEquals($_SERVER['CONTENT_TYPE'], $Request->getHeader('content_type'));
    }

    public function testConstructPHPAuth()
    {
        $_SERVER['PHP_AUTH_USER'] = 'user';
        $_SERVER['PHP_AUTH_PW'] = 'pw';

        $Request = new Request();
        $this->assertInstanceOf('\moss\http\request\RequestInterface', $Request);
        $this->assertEquals($_SERVER['PHP_AUTH_USER'], $Request->getServer('PHP_AUTH_USER'));
        $this->assertEquals($_SERVER['PHP_AUTH_PW'], $Request->getServer('PHP_AUTH_PW'));
        $this->assertEquals($_SERVER['PHP_AUTH_USER'], $Request->getHeader('php_auth_user'));
        $this->assertEquals($_SERVER['PHP_AUTH_PW'], $Request->getHeader('php_auth_pw'));
    }

    public function testConstructHTTPAuth()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'basic ' . base64_encode('user:pw');

        $Request = new Request();
        $this->assertInstanceOf('\moss\http\request\RequestInterface', $Request);
        $this->assertEquals($_SERVER['HTTP_AUTHORIZATION'], $Request->getServer('HTTP_AUTHORIZATION'));
        $this->assertEquals('Basic ' . base64_encode('user:pw'), $Request->getHeader('authorization'));
        $this->assertEquals('user', $Request->getHeader('php_auth_user'));
        $this->assertEquals('pw', $Request->getHeader('php_auth_pw'));
    }

    public function testConstructHTTPAuthRedirect()
    {
        $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] = 'basic ' . base64_encode('user:pw');

        $Request = new Request();
        $this->assertInstanceOf('\moss\http\request\RequestInterface', $Request);
        $this->assertEquals($_SERVER['REDIRECT_HTTP_AUTHORIZATION'], $Request->getServer('REDIRECT_HTTP_AUTHORIZATION'));
        $this->assertEquals('user', $Request->getHeader('php_auth_user'));
        $this->assertEquals('pw', $Request->getHeader('php_auth_pw'));
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

        $Request = new Request();
        $this->assertEquals('foo', $Request->getSession('foo'));
    }

    public function testSessionDeep()
    {
        $_SESSION['foo'] = array('bar' => 'bar');

        $Request = new Request();
        $this->assertEquals('bar', $Request->getSession('foo.bar'));
    }

    public function testCookie()
    {
        $_COOKIE['foo'] = 'foo';

        $Request = new Request();
        $this->assertEquals('foo', $Request->getCookie('foo'));
    }

    public function testCookieDeep()
    {
        $_COOKIE['foo'] = array('bar' => 'bar');

        $Request = new Request();
        $this->assertEquals('bar', $Request->getCookie('foo.bar'));
    }

    public function getServerBlank()
    {
        $Request = new Request();
        $this->assertNull($Request->getServer('foobar'));
    }

    public function testGetHeaderBlank()
    {
        $Request = new Request();
        $this->assertNull($Request->getHeader('foobar'));
    }

    public function testGetQuery()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = array(
            'foo' => 'bar',
            'controller' => 'foobar',
            'locale' => 'pl',
            'format' => 'json'
        );

        $Request = new Request();

        $this->assertEquals('bar', $Request->getQuery('foo'));
        $this->assertEquals('foobar', $Request->getQuery('controller'));
        $this->assertEquals('pl', $Request->getQuery('locale'));
        $this->assertEquals('json', $Request->getQuery('format'));
    }

    public function testSetQuery()
    {
        $Request = new Request();

        $this->assertEquals('bar', $Request->getQuery('foo', 'bar'));
        $this->assertEquals('foobar', $Request->getQuery('controller', 'foobar'));
        $this->assertEquals('pl', $Request->getQuery('locale', 'pl'));
        $this->assertEquals('json', $Request->getQuery('format', 'json'));
        $this->assertEquals('yada', $Request->getQuery('foo.bar', 'yada'));
        $this->assertEquals('deep', $Request->getQuery('f.o.o.b.a.r', 'deep'));
    }

    public function testGetPost()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(
            'foo' => 'bar',
            'controller' => 'foobar',
            'locale' => 'pl',
            'format' => 'json'
        );

        $Request = new Request();
        $this->assertEquals('bar', $Request->getPost('foo'));
        $this->assertEquals('foobar', $Request->getPost('controller'));
        $this->assertEquals('pl', $Request->getPost('locale'));
        $this->assertEquals('json', $Request->getPost('format'));
    }

    public function testSetPost()
    {
        $Request = new Request();
        $this->assertEquals('bar', $Request->getPost('foo', 'bar'));
        $this->assertEquals('foobar', $Request->getPost('controller', 'foobar'));
        $this->assertEquals('pl', $Request->getPost('locale', 'pl'));
        $this->assertEquals('json', $Request->getPost('format', 'json'));
        $this->assertEquals('yada', $Request->getPost('foo.bar.zope', 'yada'));
        $this->assertEquals('deep', $Request->getPost('f.o.o.b.a.r', 'deep'));
    }

    public function testGetFile()
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
        $this->assertEquals($result, $Request->getFile('foo'));
    }

    public function testGetFileDeep()
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
        $this->assertEquals($result, $Request->getFile('foo'));
    }

    public function testGetFileError()
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
        $this->assertEquals($result['bar1'], $Request->getFile('bar1'));
        $this->assertEquals($result['bar2'], $Request->getFile('bar2'));
        $this->assertEquals($result['bar3'], $Request->getFile('bar3'));
        $this->assertEquals($result['bar4'], $Request->getFile('bar4'));
        $this->assertEquals($result['bar5'], $Request->getFile('bar5'));
        $this->assertEquals($result['bar6'], $Request->getFile('bar6'));
        $this->assertEquals($result['bar7'], $Request->getFile('bar7'));
        $this->assertEquals($result['bar8'], $Request->getFile('bar8'));
    }

    public function testIsXHRFalse()
    {
        $Request = new Request();
        $this->assertFalse($Request->isAjax());
    }

    public function testIsXHRTrue()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
        $Request = new Request();
        $this->assertTrue($Request->isAjax());
    }

    public function testGetMethodCLI()
    {
        $_SERVER['REQUEST_METHOD'] = null;
        $Request = new Request();
        $this->assertEquals('CLI', $Request->method());
    }

    public function testGetMethodGET()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $Request = new Request();
        $this->assertEquals('GET', $Request->method());
    }

    public function testGetMethodPOST()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $Request = new Request();
        $this->assertEquals('POST', $Request->method());
    }

    public function testGetMethodPUT()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $Request = new Request();
        $this->assertEquals('PUT', $Request->method());
    }

    public function testGetMethodDELETE()
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $Request = new Request();
        $this->assertEquals('DELETE', $Request->method());
    }

    public function testGetSchema()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
        $Request = new Request();
        $this->assertEquals('HTTP/1.0', $Request->schema());
    }

    public function testGetDomain()
    {
        $_SERVER['HTTP_HOST'] = 'foo.test.com';
        $Request = new Request();
        $this->assertEquals('foo.test.com', $Request->host());
    }

    public function testGetDir()
    {
        $_SERVER['PHP_SELF'] = '/web/';
        $_SERVER['HTTP_HOST'] = 'test.com';

        $Request = new Request();
        $this->assertEquals('/web/', $Request->dir());
    }

    public function testGetBaseName()
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

    public function testGetClientIpRemote()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $Request = new Request();
        $this->assertEquals('127.0.0.1', $Request->clientIp());
    }

    public function testGetClientIpForwarded()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';

        $Request = new Request();
        $this->assertEquals('127.0.0.1', $Request->clientIp());
    }

    public function testGetClientIpHTTPClientIp()
    {
        $_SERVER['HTTP_CLIENT_IP'] = '127.0.0.1';

        $Request = new Request();
        $this->assertEquals('127.0.0.1', $Request->clientIp());
    }

    public function testGetController()
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

    public function testGetURI()
    {
        $_SERVER['REQUEST_URI'] = '/foo/index.html?foo=bar';

        $Request = new Request();
        $this->assertEquals('/foo/index.html', $Request->url());
    }

    public function testGetEmptyInvalidRedirect()
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

    public function testGetInvalidRedirect()
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

    public function testGetReferer()
    {
        $_SERVER['HTTP_REFERER'] = 'test.com';

        $Request = new Request();
        $this->assertEquals('test.com', $Request->referrer());
    }

    public function testGetLocaleFromHeader()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'pl,en-us;q=0.7,en;q=0.3';

        $Request = new Request();
        $this->assertEquals('pl', $Request->locale());
    }

    public function testGetLocaleFromQuery()
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

    public function testGetFormat()
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
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
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertInstanceOf('\moss\http\request\RequestInterface', $request);
    }

    public function testConstructContent()
    {
        $_SERVER['CONTENT_LENGTH'] = 123456;
        $_SERVER['CONTENT_MD5'] = 'someMD5';
        $_SERVER['CONTENT_TYPE'] = 'text/plan';

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertInstanceOf('\moss\http\request\RequestInterface', $request);
        $this->assertEquals($_SERVER['CONTENT_LENGTH'], $request->server('CONTENT_LENGTH'));
        $this->assertEquals($_SERVER['CONTENT_MD5'], $request->server('CONTENT_MD5'));
        $this->assertEquals($_SERVER['CONTENT_TYPE'], $request->server('CONTENT_TYPE'));
        $this->assertEquals($_SERVER['CONTENT_LENGTH'], $request->header('content_length'));
        $this->assertEquals($_SERVER['CONTENT_MD5'], $request->header('content_md5'));
        $this->assertEquals($_SERVER['CONTENT_TYPE'], $request->header('content_type'));
    }

    public function testConstructWithMagicQuotes()
    {
        if (version_compare(phpversion(), '6.0.0-dev', '<') && get_magic_quotes_gpc()) {
            $this->markTestSkipped('Magic quotes are off');
        }
    }

    public function testSession()
    {
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertInstanceOf('\moss\http\session\SessionInterface', $request->session());
        $this->assertInstanceOf('\moss\http\session\SessionInterface', $request->session);
    }

    public function testCookie()
    {
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertInstanceOf('\moss\http\cookie\CookieInterface', $request->cookie());
        $this->assertInstanceOf('\moss\http\cookie\CookieInterface', $request->cookie);
    }

    public function getServerBlank()
    {
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertNull($request->server('foobar'));
    }

    public function testHeaderBlank()
    {
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertNull($request->header('foobar'));
    }

    public function testConstructPHPAuth()
    {
        $_SERVER['PHP_AUTH_USER'] = 'user';
        $_SERVER['PHP_AUTH_PW'] = 'pw';

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertInstanceOf('\moss\http\request\RequestInterface', $request);
        $this->assertEquals($_SERVER['PHP_AUTH_USER'], $request->server('PHP_AUTH_USER'));
        $this->assertEquals($_SERVER['PHP_AUTH_PW'], $request->server('PHP_AUTH_PW'));
        $this->assertEquals($_SERVER['PHP_AUTH_USER'], $request->header('php_auth_user'));
        $this->assertEquals($_SERVER['PHP_AUTH_PW'], $request->header('php_auth_pw'));
    }

    public function testConstructHTTPAuth()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'basic ' . base64_encode('user:pw');

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertInstanceOf('\moss\http\request\RequestInterface', $request);
        $this->assertEquals($_SERVER['HTTP_AUTHORIZATION'], $request->server('HTTP_AUTHORIZATION'));
        $this->assertEquals('basic ' . base64_encode('user:pw'), $request->header('authorization'));
        $this->assertEquals('user', $request->header('php_auth_user'));
        $this->assertEquals('pw', $request->header('php_auth_pw'));
    }

    public function testConstructHTTPAuthRedirect()
    {
        $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] = 'basic ' . base64_encode('user:pw');

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertInstanceOf('\moss\http\request\RequestInterface', $request);
        $this->assertEquals($_SERVER['REDIRECT_HTTP_AUTHORIZATION'], $request->server('REDIRECT_HTTP_AUTHORIZATION'));
        $this->assertEquals('user', $request->header('php_auth_user'));
        $this->assertEquals('pw', $request->header('php_auth_pw'));
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

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );

        $this->assertEquals('bar', $request->query->get('foo'));
        $this->assertEquals('foobar', $request->query->get('controller'));
        $this->assertEquals('pl', $request->query->get('locale'));
        $this->assertEquals('json', $request->query->get('format'));
    }

    public function testSetQuery()
    {
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );

        $this->assertEquals('bar', $request->query->get('foo', 'bar'));
        $this->assertEquals('foobar', $request->query->get('controller', 'foobar'));
        $this->assertEquals('pl', $request->query->get('locale', 'pl'));
        $this->assertEquals('json', $request->query->get('format', 'json'));
        $this->assertEquals('yada', $request->query->get('foo.bar', 'yada'));
        $this->assertEquals('deep', $request->query->get('f.o.o.b.a.r', 'deep'));
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

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('bar', $request->post->get('foo'));
        $this->assertEquals('foobar', $request->post->get('controller'));
        $this->assertEquals('pl', $request->post->get('locale'));
        $this->assertEquals('json', $request->post->get('format'));
    }

    public function testSetPost()
    {
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('bar', $request->post->get('foo', 'bar'));
        $this->assertEquals('foobar', $request->post->get('controller', 'foobar'));
        $this->assertEquals('pl', $request->post->get('locale', 'pl'));
        $this->assertEquals('json', $request->post->get('format', 'json'));
        $this->assertEquals('yada', $request->post->get('foo.bar.zope', 'yada'));
        $this->assertEquals(
             'deep', $request->post()
                             ->get('f.o.o.b.a.r', 'deep')
        );
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
            'size' => 123
        );

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals($result, $request->files->get('foo'));
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
                'size' => 123
            ),
            'bar' => array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'bar_tmp',
                'error' => 0,
                'size' => 456
            )
        );

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals($result, $request->files->get('foo'));
    }

    /**
     * @dataProvider errorDataProvider
     */
    public function testFileError($key, $files, $result)
    {
        $_FILES[$key] = $files;

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals(
             $result, $request->files->uploaded($key)
                                     ->getRaw()
        );
    }

    public function errorDataProvider()
    {
        return array(
            array(
                'bar1',
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 1,
                    'size' => 0
                ),
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 1,
                    'errorMessage' => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                    'size' => 0
                )
            ),
            array(
                'bar2',
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 2,
                    'size' => 0
                ),
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 2,
                    'errorMessage' => 'The uploaded file exceeds the MAX_FILE_SIZE directive specified in HTML form.',
                    'size' => 0
                )
            ),

            array(
                'bar3',
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 3,
                    'size' => 0
                ),
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 3,
                    'errorMessage' => 'The uploaded file was only partially uploaded.',
                    'size' => 0
                )
            ),
            array(
                'bar4',
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 4,
                    'size' => 0
                ),
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 4,
                    'errorMessage' => 'No file was uploaded.',
                    'size' => 0
                )
            ),
            array(
                'bar5', array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 5,
                'size' => 0
            ),
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 5,
                    'errorMessage' => 'Unknown error occurred.',
                    'size' => 0
                )
            ),
            array(
                'bar6', array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 6,
                'size' => 0
            ),
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 6,
                    'errorMessage' => 'Missing a temporary folder.',
                    'size' => 0
                )
            ),
            array(
                'bar7', array(
                'name' => 'bar.txt',
                'type' => 'text/plain',
                'tmp_name' => 'whatever2',
                'error' => 7,
                'size' => 0
            ),
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 7,
                    'errorMessage' => 'Failed to write file to disk.',
                    'size' => 0
                )
            ),
            array(
                'bar8',
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 8,
                    'size' => 0
                ),
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 8,
                    'errorMessage' => 'A PHP extension stopped the file upload.',
                    'size' => 0
                )
            )
        );
    }

    public function testIsSecureFalse()
    {
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertFalse($request->isSecure());
    }

    public function testIsSecureTrue()
    {
        $_SERVER['HTTPS'] = 'ON';
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertTrue($request->isSecure());
    }

    public function testIsAjaxFalse()
    {
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertFalse($request->isAjax());
    }

    public function testIsAjaxTrue()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHTTPREQUEST';
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertTrue($request->isAjax());
    }

    public function testMethodCLI()
    {
        $_SERVER['REQUEST_METHOD'] = null;
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('CLI', $request->method());
    }

    public function testMethodGET()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('GET', $request->method());
    }

    public function testMethodPOST()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('POST', $request->method());
    }

    public function testMethodPUT()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('PUT', $request->method());
    }

    public function testMethodDELETE()
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('DELETE', $request->method());
    }

    public function testSchema()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'http';
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('http', $request->schema());
    }

    public function testSchemaSecure()
    {
        $_SERVER['HTTPS'] = 'on';
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('https', $request->schema());
    }

    public function testDomain()
    {
        $_SERVER['HTTP_HOST'] = 'foo.test.com';
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('foo.test.com', $request->host());
    }

    /**
     * @dataProvider dirProvider
     */
    public function testDir($document, $script, $dir)
    {
        $_SERVER['DOCUMENT_ROOT'] = $document;
        $_SERVER['SCRIPT_FILENAME'] = $script;

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals($dir, $request->dir());
    }

    public function dirProvider() {
        return array(
            array('c:/xampp/htdocs/moss/web/', 'c:/xampp/htdocs/moss/web/index.php', '/'),
            array('c:/xampp/htdocs/', 'c:/xampp/htdocs/moss/web/index.php', '/moss/web/'),
            array('/home/foo/www/moss', '/home/foo/www/moss/web/index.php', '/web/'),
            array('/home/foo/www/moss/web', '/home/foo/www/moss/web/index.php', '/'),
        );
    }

    public function testBaseName()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
        $_SERVER['REQUEST_URI'] = '/foo/index.html?foo=bar';
        $_SERVER['DOCUMENT_ROOT'] = '/home/foo/www/moss/web/';
        $_SERVER['SCRIPT_FILENAME'] = '/home/foo/www/moss/web/index.php';
        $_SERVER['HTTP_HOST'] = 'test.com';

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('http://test.com/', $request->baseName());
    }

    public function testSetBaseName()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
        $_SERVER['REQUEST_URI'] = '/foo/index.html?foo=bar';
        $_SERVER['DOCUMENT_ROOT'] = '/home/foo/www/moss/web/';
        $_SERVER['SCRIPT_FILENAME'] = '/home/foo/www/moss/web/index.php';
        $_SERVER['HTTP_HOST'] = 'test.com';

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('http://test.com/', $request->baseName());

        $request->baseName('http://yada.com/');
        $this->assertEquals('http://yada.com/', $request->baseName());
    }

    public function testClientIpRemote()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('127.0.0.1', $request->clientIp());
    }

    public function testClientIpForwarded()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('127.0.0.1', $request->clientIp());
    }

    public function testClientIpHTTPClientIp()
    {
        $_SERVER['HTTP_CLIENT_IP'] = '127.0.0.1';

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('127.0.0.1', $request->clientIp());
    }

    public function testController()
    {
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals(null, $request->controller());
    }

    public function testSetController()
    {
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals(null, $request->controller());
        $this->assertEquals('foobar', $request->controller('foobar'));
    }

    public function testURI()
    {
        $_SERVER['REQUEST_URI'] = '/foo/index.html?foo=bar';

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('/foo/index.html', $request->path());
    }

    public function testEmptyInvalidRedirect()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
        $_SERVER['REQUEST_URI'] = '/web/foo/index.html?foo=bar';
        $_SERVER['DOCUMENT_ROOT'] = '/home/foo/www/moss/web/';
        $_SERVER['SCRIPT_FILENAME'] = '/home/foo/www/moss/web/index.php';
        $_SERVER['HTTP_HOST'] = 'test.com';
        $_SERVER['REDIRECT_URL'] = null;

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('/', $request->dir());
        $this->assertEquals('/web/foo/index.html', $request->path());
    }

    public function testInvalidRedirect()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
        $_SERVER['REQUEST_URI'] = '/invalid/redirect/web/foo/index.html?foo=bar';
        $_SERVER['DOCUMENT_ROOT'] = '/home/foo/www/moss/';
        $_SERVER['SCRIPT_FILENAME'] = '/home/foo/www/moss/invalid/redirect/web/index.php';
        $_SERVER['HTTP_HOST'] = 'test.com';
        $_SERVER['REDIRECT_URL'] = '/';

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('/', $request->dir());
        $this->assertEquals('/foo/index.html', $request->path());
    }

    public function testReferer()
    {
        $_SERVER['HTTP_REFERER'] = 'test.com';

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('test.com', $request->referrer());
    }

    public function testLocaleFromHeader()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'pl,en-us;q=0.7,en;q=0.3';

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('pl', $request->locale());
    }

    public function testLocaleFromQuery()
    {
        $_GET['locale'] = 'pl';

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('pl', $request->locale());
    }

    public function testSetLocale()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'pl,en-us;q=0.7,en;q=0.3';

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $request->locale('en');
        $this->assertEquals('en', $request->locale());
    }

    public function testFormat()
    {
        $_GET['format'] = 'json';

        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $this->assertEquals('json', $request->format());
    }

    public function testSetFormat()
    {
        $request = new Request(
            $this->getMock('\moss\http\session\SessionInterface'),
            $this->getMock('\moss\http\cookie\CookieInterface')
        );
        $request->format('json');
        $this->assertEquals('json', $request->format());
    }
}
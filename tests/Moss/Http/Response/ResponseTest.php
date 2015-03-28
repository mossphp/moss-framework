<?php
namespace Moss\Http\Response;

if (!function_exists('\Moss\Http\Response\headers_sent')) {
    function headers_sent() { return false; }
}

if (!function_exists('\Moss\Http\Response\header')) {
    function header($header) { echo $header . PHP_EOL; }
}

if (!function_exists('\Moss\Http\Response\setcookie')) {
    function setcookie() { echo implode(', ', func_get_args()) . PHP_EOL; }
}

class ResponseTest extends \PHPUnit_Framework_TestCase
{


    public function testScalarContent()
    {
        $response = new Response('Foo');
        $this->assertEquals('Foo', $response->content());
    }

    public function testObjectContent()
    {
        $response = new Response(new \SplFileInfo(__FILE__));
        $this->assertEquals(__FILE__, $response->content());
    }

    /**
     * @expectedException \Moss\Http\response\ResponseException
     * @expectedExceptionMessage Response content must be a scalar or object with __toString() method "array" given.
     */
    public function testInvalidContent()
    {
        new Response([]);
    }

    public function testValidStatus()
    {
        $response = new Response('Foo', 200);
        $this->assertEquals(200, $response->status());
    }

    /**
     * @expectedException \Moss\Http\response\ResponseException
     * @expectedExceptionMessage Unsupported status code "999"
     */
    public function testInvalidStatus()
    {
        new Response('Foo', 999);
    }

    public function testAllHeaders()
    {
        $headers = [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache'
        ];
        $response = new Response('Foo', 200);
        $this->assertEquals(
            $headers,
            $response->header($headers)
                ->all()
        );
    }

    public function testGetHeader()
    {
        $response = new Response('Foo', 200);
        $this->assertEquals('foo', $response->header()->get('foo', 'foo'));
    }

    public function testRemoveHeader()
    {
        $response = new Response('Foo', 200);
        $response->makeNoCache();
        $response->header()
            ->remove('Cache-Control');
        $this->assertEquals(
            null, $response->header()->get('Cache-Control')
        );
    }

    public function testNoCache()
    {
        $response = new Response('Foo', 200);
        $response->makeNoCache();
        $this->assertEquals('no-cache', $response->header()->get('Cache-Control'));
        $this->assertEquals('no-cache', $response->header()->get('Pragma'));
    }

    public function testPublic()
    {
        $response = new Response('Foo', 200);
        $response->makePublic();
        $this->assertEquals('public', $response->header()->get('Cache-Control'));
        $this->assertEquals('public', $response->header()->get('Pragma'));
    }

    public function testPrivate()
    {
        $response = new Response('Foo', 200);
        $response->makePrivate();
        $this->assertEquals('private', $response->header()->get('Cache-Control'));
        $this->assertEquals('private', $response->header()->get('Pragma'));
    }

    public function testProtocol()
    {
        $response = new Response('Foo', 200);
        $this->assertEquals('HTTP/1.1', $response->protocol());
        $this->assertEquals('HTTP/1.0', $response->protocol('HTTP/1.0'));
    }

    public function testSendHeaders()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('name')->willReturn('foo');
        $cookie->expects($this->any())->method('value')->willReturn('bar');
        $cookie->expects($this->any())->method('ttl')->willReturn(1423559410);
        $cookie->expects($this->any())->method('path')->willReturn('/');
        $cookie->expects($this->any())->method('domain')->willReturn('domain');
        $cookie->expects($this->any())->method('isSecure')->willReturn(true);
        $cookie->expects($this->any())->method('isHttpOnly')->willReturn(true);

        $expected = [
            'HTTP/1.1 200 OK',
            'Content-Type: text/html; charset=UTF-8',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'foo, bar, 1423559410, /, domain, 1, 1'
        ];

        $this->expectOutputString(implode(PHP_EOL, $expected) . PHP_EOL);

        $response = new Response('Foo', 200);
        $response->cookie()->set($cookie);
        $response->sendHeaders();
    }

    public function testSendContent()
    {
        $expected = ['Foo'];
        $this->expectOutputString(implode(PHP_EOL, $expected));

        $response = new Response('Foo', 200);
        $response->sendContent();
    }

    public function testSend()
    {
        $expected = ['HTTP/1.1 200 OK', 'Content-Type: text/html; charset=UTF-8', 'Cache-Control: no-cache', 'Pragma: no-cache', 'Foo'];
        $this->expectOutputString(implode(PHP_EOL, $expected));

        $response = new Response('Foo', 200);
        $response->send();
    }

    public function testToString()
    {
        $cookie = $this->getMock('\Moss\Http\Response\CookieInterface');
        $cookie->expects($this->any())->method('__toString')->willReturn('cookieString');

        $expected = [
            'HTTP/1.1 200 OK',
            'Content-Type: text/html; charset=UTF-8',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'Set-Cookie: cookieString',
            'Foo'
        ];

        $response = new Response('Foo', 200);
        $response->cookie()->set($cookie);

        $this->assertEquals(implode(PHP_EOL, $expected), (string) $response);
    }

}

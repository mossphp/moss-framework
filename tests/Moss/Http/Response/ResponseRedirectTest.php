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

class ResponseRedirectTest extends \PHPUnit_Framework_TestCase
{
    public function testAddress()
    {
        $response = new ResponseRedirect('http://127.0.0.1/');
        $this->assertEquals('http://127.0.0.1/', $response->address());
    }

    public function testSendHeaders()
    {
        $expected = [
            'HTTP/1.1 302 Found',
            'Content-Type: text/html; charset=UTF-8',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'Location: http://127.0.0.1/'
        ];
        $this->expectOutputString(implode(PHP_EOL, $expected) . PHP_EOL);

        $response = new ResponseRedirect('http://127.0.0.1/');
        $response->sendHeaders();
    }

    public function testSendContent()
    {
        $expected = ['Redirecting...'];
        $this->expectOutputString(implode(PHP_EOL, $expected));

        $response = new ResponseRedirect('http://127.0.0.1/');
        $response->sendContent();
    }

    public function testSend()
    {
        $expected = [
            'HTTP/1.1 302 Found',
            'Content-Type: text/html; charset=UTF-8',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'Location: http://127.0.0.1/',
            'Redirecting...'
        ];
        $this->expectOutputString(implode(PHP_EOL, $expected));

        $response = new ResponseRedirect('http://127.0.0.1/');
        $response->send();
    }

    public function testToString()
    {
        $response = new ResponseRedirect('http://127.0.0.1/');
        $expected = [
            'HTTP/1.1 302 Found',
            'Content-Type: text/html; charset=UTF-8',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'Location: http://127.0.0.1/',
            'Redirecting...'
        ];

        $this->assertEquals(implode(PHP_EOL, $expected), (string) $response);
    }
}

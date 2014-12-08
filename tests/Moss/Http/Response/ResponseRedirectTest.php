<?php
namespace Moss\Http\Response;


class ResponseRedirectTest extends \PHPUnit_Framework_TestCase
{


    public function testAddress()
    {
        $response = new ResponseRedirect('http://127.0.0.1/');
        $this->assertEquals('http://127.0.0.1/', $response->address());
    }

    public function testDelay()
    {
        $response = new ResponseRedirect('http://127.0.0.1/', 10);
        $this->assertEquals(10, $response->delay());
    }

    public function testSendHeadersWithDelay()
    {
        ob_start();
        $response = new ResponseRedirect('http://127.0.0.1/', 10);
        $response->sendHeaders();
        $result = ob_get_clean();

        $expected = ['HTTP/1.1 302 Found', 'Refresh: 10; URL=http://127.0.0.1/'];
        $this->assertEquals(implode(PHP_EOL, $expected) . PHP_EOL, $result);
    }

    public function testSendHeadersWithoutDelay()
    {
        ob_start();
        $response = new ResponseRedirect('http://127.0.0.1/');
        $response->sendHeaders();
        $result = ob_get_clean();

        $expected = ['HTTP/1.1 302 Found', 'Location: http://127.0.0.1/'];
        $this->assertEquals(implode(PHP_EOL, $expected) . PHP_EOL, $result);
    }

    public function testSendContent()
    {
        ob_start();
        $response = new ResponseRedirect('http://127.0.0.1/', 10);
        $response->sendContent();
        $result = ob_get_clean();

        $expected = ['Redirecting...'];
        $this->assertEquals(implode(PHP_EOL, $expected), $result);
    }

    public function testSendWithDelay()
    {
        ob_start();
        $response = new ResponseRedirect('http://127.0.0.1/', 10);
        $response->send();
        $result = ob_get_clean();

        $expected = ['HTTP/1.1 302 Found', 'Refresh: 10; URL=http://127.0.0.1/', 'Redirecting...'];
        $this->assertEquals(implode(PHP_EOL, $expected), $result);
    }

    public function testSendWithoutDelay()
    {
        ob_start();
        $response = new ResponseRedirect('http://127.0.0.1/');
        $response->send();
        $result = ob_get_clean();

        $expected = ['HTTP/1.1 302 Found', 'Location: http://127.0.0.1/', 'Redirecting...'];
        $this->assertEquals(implode(PHP_EOL, $expected), $result);
    }

    public function testToString()
    {
        $response = new ResponseRedirect('http://127.0.0.1/', 10);
        $expected = ['HTTP/1.1 302 Found', 'Refresh: 10; URL=http://127.0.0.1/', 'Redirecting...'];
        $this->assertEquals(implode(PHP_EOL, $expected), (string) $response);
    }
}

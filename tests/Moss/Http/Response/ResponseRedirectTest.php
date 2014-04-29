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

//    public function testToString()
//    {
//        $response = new ResponseRedirect('http://127.0.0.1/', 10);
//
//        $expected = 'HTTP/1.1 302 Found
//Refresh: 10; URL=http://127.0.0.1/
//
//Redirecting...';
//
//        $this->assertEquals($expected, (string) $response);
//    }
}

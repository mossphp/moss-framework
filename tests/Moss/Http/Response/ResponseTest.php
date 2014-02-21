<?php
namespace Moss\Http\Response;


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
        new Response(array());
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
        $headers = array(
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache'
        );
        $response = new Response('Foo', 200);
        $this->assertEquals(
             $headers, $response->header($headers)
                                ->all()
        );
    }

    public function testGetHeader()
    {
        $response = new Response('Foo', 200);
        $this->assertEquals('foo', $response->header->get('foo', 'foo'));
    }

    public function testRemoveHeader()
    {
        $response = new Response('Foo', 200);
        $response->makeNoCache();
        $response->header()
                 ->remove('Cache-Control');
        $this->assertEquals(null, $response->header->get('Cache-Control')
        );
    }

    public function testNoCache()
    {
        $response = new Response('Foo', 200);
        $response->makeNoCache();
        $this->assertEquals('no-cache', $response->header->get('Cache-Control'));
        $this->assertEquals('no-cache', $response->header->get('Pragma'));
    }

    public function testPublic()
    {
        $response = new Response('Foo', 200);
        $response->makePublic();
        $this->assertEquals('public', $response->header->get('Cache-Control'));
        $this->assertEquals('public', $response->header->get('Pragma'));
    }

    public function testPrivate()
    {
        $response = new Response('Foo', 200);
        $response->makePrivate();
        $this->assertEquals('private', $response->header->get('Cache-Control'));
        $this->assertEquals('private', $response->header->get('Pragma'));
    }

    public function testProtocol()
    {
        $response = new Response('Foo', 200);
        $this->assertEquals('HTTP/1.1', $response->protocol());
        $this->assertEquals('HTTP/1.0', $response->protocol('HTTP/1.0'));
    }

//    public function testToString()
//    {
//        $response = new Response('Foo', 200);
//
//        $expected = 'HTTP/1.1 200 OK
//Content-Type: text/html; charset=UTF-8
//Cache-Control: no-cache
//Pragma: no-cache
//
//Foo';
//
//        $this->assertEquals($expected, (string) $response);
//    }

}

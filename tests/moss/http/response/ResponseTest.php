<?php
namespace moss\http\response;


class ResponseTest extends \PHPUnit_Framework_TestCase
{


    public function testScalarContent()
    {
        $Response = new Response('Foo');
        $this->assertEquals('Foo', $Response->content());
    }

    public function testObjectContent()
    {
        $Response = new Response(new \SplFileInfo(__FILE__));
        $this->assertEquals(__FILE__, $Response->content());
    }

    /**
     * @expectedException \moss\http\response\ResponseException
     * @expectedExceptionMessage Response content must be a scalar or object with __toString() method "array" given.
     */
    public function testInvalidContent()
    {
        new Response(array());
    }

    public function testValidStatus()
    {
        $Response = new Response('Foo', 200);
        $this->assertEquals(200, $Response->status());
    }

    /**
     * @expectedException \moss\http\response\ResponseException
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
        $Response = new Response('Foo', 200);
        $this->assertEquals($headers, $Response->header($headers)->all());
    }

    public function testGetHeader()
    {
        $Response = new Response('Foo', 200);
        $this->assertEquals('foo', $Response->header()->get('foo', 'foo'));
    }

    public function testRemoveHeader()
    {
        $Response = new Response('Foo', 200);
        $Response->makeNoCache();
        $Response->header()->remove('Cache-Control');
        $this->assertEquals(null, $Response->header()->get('Cache-Control'));
    }

    public function testNoCache()
    {
        $Response = new Response('Foo', 200);
        $Response->makeNoCache();
        $this->assertEquals('no-cache', $Response->header()->get('Cache-Control'));
        $this->assertEquals('no-cache', $Response->header()->get('Pragma'));
    }

    public function testPublic()
    {
        $Response = new Response('Foo', 200);
        $Response->makePublic();
        $this->assertEquals('public', $Response->header()->get('Cache-Control'));
        $this->assertEquals('public', $Response->header()->get('Pragma'));
    }

    public function testPrivate()
    {
        $Response = new Response('Foo', 200);
        $Response->makePrivate();
        $this->assertEquals('private', $Response->header()->get('Cache-Control'));
        $this->assertEquals('private', $Response->header()->get('Pragma'));
    }

    public function testProtocol()
    {
        $Response = new Response('Foo', 200);
        $this->assertEquals('HTTP/1.1', $Response->protocol());
        $this->assertEquals('HTTP/1.0', $Response->protocol('HTTP/1.0'));
    }

    public function testToString() {
        $Response = new Response('Foo', 200);

        $result = 'HTTP/1.1 200 OK
Content-Type: text/html; charset=UTF-8
Cache-Control: no-cache
Pragma: no-cache

Foo';

        $this->assertEquals($result, (string) $Response);
    }

}

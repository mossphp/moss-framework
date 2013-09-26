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
     */
    public function testInvaliudContent()
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
        $this->assertEquals($headers, $Response->headers($headers));
    }

    public function testGetHeader()
    {
        $Response = new Response('Foo', 200);
        $this->assertEquals('foo', $Response->getHeader('foo', 'foo'));
    }

    public function testRemoveHeader()
    {
        $Response = new Response('Foo', 200);
        $Response->makeNoCache();
        $Response->removeHeader('Cache-Control');
        $this->assertEquals(null, $Response->getHeader('Cache-Control'));
    }

    public function testNoCache()
    {
        $Response = new Response('Foo', 200);
        $Response->makeNoCache();
        $this->assertEquals('no-cache', $Response->getHeader('Cache-Control'));
        $this->assertEquals('no-cache', $Response->getHeader('Pragma'));
    }

    public function testPublic()
    {
        $Response = new Response('Foo', 200);
        $Response->makePublic();
        $this->assertEquals('public', $Response->getHeader('Cache-Control'));
        $this->assertEquals('public', $Response->getHeader('Pragma'));
    }

    public function testPrivate()
    {
        $Response = new Response('Foo', 200);
        $Response->makePrivate();
        $this->assertEquals('private', $Response->getHeader('Cache-Control'));
        $this->assertEquals('private', $Response->getHeader('Pragma'));
    }

    public function testProtocol()
    {
        $Response = new Response('Foo', 200);
        $this->assertEquals('HTTP/1.1', $Response->protocol());
        $this->assertEquals('HTTP/1.0', $Response->protocol('HTTP/1.0'));
    }

    public function testSendHeaders()
    {
        $this->markTestIncomplete();
    }

    public function testSendContent()
    {
        $this->markTestIncomplete();
    }

    public function testSend()
    {
        $this->markTestIncomplete();
    }

}

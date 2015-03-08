<?php
namespace Moss\Http\Response;

if(!function_exists('\Moss\Http\Response\time')) {
    function time() { return 1423559410; }
}

class CookieTest extends \PHPUnit_Framework_TestCase
{

    public function testName()
    {
        $cookie = new Cookie('foo');
        $this->assertEquals('foo', $cookie->name());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cookie name
     *
     * @dataProvider invalidNameProvider
     */
    public function testNameWithInvalidChars($name)
    {
        new Cookie($name);
    }

    public function invalidNameProvider()
    {
        return [
            ['='],
            [','],
            [';'],
            [' '],
            ["\t"],
            ["\r"],
            ["\n"],
            ["\013"],
            ["\014"]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cookie name cannot be empty
     */
    public function testEmptyName()
    {
        new Cookie('');
    }


    public function testValue()
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertEquals('bar', $cookie->value());
    }

    public function testDomain()
    {
        $cookie = new Cookie('foo', 'bar');
        $cookie->domain('domain');
        $this->assertEquals('domain', $cookie->domain());
    }

    public function testPath()
    {
        $cookie = new Cookie('foo', 'bar');
        $cookie->path('/yada/');
        $this->assertEquals('/yada/', $cookie->path());
    }

    /**
     * @dataProvider ttlProvider
     */
    public function testTTL($ttl, $expected)
    {
        $cookie = new Cookie('foo', 'bar');
        $cookie->ttl($ttl);
        $this->assertEquals($expected, $cookie->ttl());
    }

    public function ttlProvider()
    {
        return [
            [null, 1423559410 + 2592000],
            [3600, 1423559410 + 3600],
            [new \DateTime('2015-02-10 10:10:10'), (new \DateTime('2015-02-10 10:10:10'))->format('U')],
        ];
    }

    public function testSecure()
    {
        $cookie = new Cookie('foo', 'bar', 3600, '/', 'domain', true);
        $this->assertTrue($cookie->isSecure());
        $cookie->secure(false);
        $this->assertFalse($cookie->isSecure());
    }

    public function testIsHttpOnly()
    {
        $cookie = new Cookie('foo', 'bar', 3600, '/', 'domain', false, true);
        $this->assertTrue($cookie->isHttpOnly());
        $cookie->httpOnly(false);
        $this->assertFalse($cookie->isHttpOnly());
    }

    public function testIsCleared()
    {
        $cookie = new Cookie('foo', 'bar', 3600, '/', 'domain', true);
        $this->assertFalse($cookie->isCleared());
        $cookie->ttl(-3600);
        $this->assertTrue($cookie->isCleared());
    }

    /**
     * @dataProvider toStringProvider
     */
    public function testToString($domain, $path, $secure, $httpOnly, $expected)
    {
        $cookie = new Cookie('foo', 'bar', new \DateTime('2015-02-10 10:10:10'), $path, $domain, $secure, $httpOnly);
        $this->assertEquals($expected, (string) $cookie);
    }

    public function toStringProvider()
    {
        return [
            [null, null, false, false, 'foo=bar; expires=Tue, 10 Feb 2015 10:10:10 +0000; path=/'],
            ['domain', null, false, false, 'foo=bar; expires=Tue, 10 Feb 2015 10:10:10 +0000; path=/; domain=domain'],
            ['domain', '/yada/', false, false, 'foo=bar; expires=Tue, 10 Feb 2015 10:10:10 +0000; path=/yada/; domain=domain'],
            ['domain', '/yada/', true, false, 'foo=bar; expires=Tue, 10 Feb 2015 10:10:10 +0000; path=/yada/; domain=domain; secure'],
            ['domain', '/yada/', true, true, 'foo=bar; expires=Tue, 10 Feb 2015 10:10:10 +0000; path=/yada/; domain=domain; secure; httponly'],
        ];
    }

    public function testDeletedToString()
    {
        $expected = 'foo=deleted; expires=Mon, 10 Feb 2014 09:10:09 +0000; path=/yada/; domain=domain; secure; httponly';

        $cookie = new Cookie('foo', null, new \DateTime('2015-02-10 10:10:10'), '/yada/', 'domain', true, true);
        $this->assertEquals($expected, (string) $cookie);
    }
}

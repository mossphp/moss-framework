<?php
namespace moss\router;

class RouteTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider patternProvider
     */
    public function testPattern($pattern, $expected)
    {
        $route = new Route($pattern, 'some:controller');
        $this->assertEquals($expected, $route->pattern());
    }

    public function patternProvider()
    {
        return array(
            array('/foo/', '/foo/'),
            array('/foo/{bar:\d}/', '/foo/{bar:\d}/'),
            array('/foo/{bar:\w}/', '/foo/{bar:\w}/'),
            array('/foo/{bar:[a-z]}/', '/foo/{bar:[a-z]}/'),
            array('/foo/{bar:.}/', '/foo/{bar:.}/'),
            array('/foo/({bar:\d}/)', '/foo/({bar:\d}/)'),
            array('/foo/{bar:\d}/({yada:\w}/)', '/foo/{bar:\d}/({yada:\w}/)'),
        );
    }

    /**
     * @dataProvider             patternQuantificationProvider
     * @expectedException \moss\router\RouteException
     * @expectedExceptionMessage Route must not end with quantification token
     */
    public function testPatternQuantificationToken($pattern)
    {
        new Route($pattern, 'some:controller');
    }

    public function patternQuantificationProvider()
    {
        return array(
            array('/foo/{bar:.?}/'),
            array('/foo/{bar:.*}/'),
            array('/foo/{bar:.+}/'),
        );
    }

    /**
     * @dataProvider requirementsProvider
     */
    public function testRequirementsFromRoute($pattern, $requirements, $expected)
    {
        $route = new Route($pattern, 'some:controller');
        $this->assertEquals($requirements, $route->requirements());
        $this->assertEquals($expected, $route->requirements($expected));
    }

    public function requirementsProvider()
    {
        return array(
            array('/foo/', array(), array()),
            array('/foo/{bar:\d}/', array('bar' => '\d+'), array('bar' => '\w+')),
            array('/foo/{bar:\d}/{yada:\w}/', array('bar' => '\d+', 'yada' => '\w+'), array('bar' => '\w+', 'yada' => '\d+')),
            array('/foo/{bar:\d}/({yada:\w}/)', array('bar' => '\d+', 'yada' => '\w*(\/)?'), array('bar' => '\w+', 'yada' => '\d*(\/)?')),
            array('/foo/{bar:\d}/({yada:\w}/)', array('bar' => '\d+', 'yada' => '\w*(\/)?'), array('bar' => '\w+', 'yada' => '\d+')),
            array('/foo/{bar}/', array('bar' => '[a-z0-9-._]+'), array('bar' => '\w+')),
            array('/foo/{bar}/{yada}/', array('bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]+'), array('bar' => '\w+', 'yada' => '\d+')),
            array('/foo/{bar}/({yada}/)', array('bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]*(\/)?'), array('bar' => '\w+', 'yada' => '\d*(\/)?')),
            array('/foo/{bar}/({yada}/)', array('bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]*(\/)?'), array('bar' => '\w+', 'yada' => '\d+')),
        );
    }

    /**
     * @dataProvider argumentsProvider
     */
    public function testArgumentsFromRoute($pattern, $arguments, $expected)
    {
        $route = new Route($pattern, 'some:controller', $arguments);
        $this->assertEquals($expected, $route->arguments());
    }

    public function argumentsProvider()
    {
        return array(
            array('/foo/', array(), array()),
            array('/foo/{bar:\d}/', array(), array('bar' => null)),
            array('/foo/{bar:\d}/', array('foo' => 1), array('foo' => 1, 'bar' => null)),
            array('/foo/{bar:\d}/{yada:\w}/', array(), array('bar' => null, 'yada' => null)),
            array('/foo/{bar:\d}/{yada:\w}/', array('foo' => 1), array('foo' => 1, 'bar' => null, 'yada' => null)),
            array('/foo/{bar:\d}/({yada:\w}/)', array(), array('bar' => null)),
            array('/foo/{bar:\d}/({yada:\w}/)', array('foo' => 1), array('foo' => 1, 'bar' => null)),
        );
    }

    /**
     * @dataProvider urlProvider
     */
    public function testMatchUrl($pattern, $url)
    {
        $route = new Route($pattern, 'some:controller');
        $this->assertTrue($route->match($this->mockRequest($url)));
    }

    /**
     * @dataProvider urlProvider
     */
    public function testMatchUrlFails($pattern, $url)
    {
        $route = new Route('/', 'some:controller');
        $this->assertFalse($route->match($this->mockRequest($url)));
    }

    public function urlProvider()
    {
        return array(
            array('/foo/', '/foo/'),
            array('/foo/{bar:\d}/', '/foo/1/'),
            array('/foo/{bar:\d}/', '/foo/123/'),

            array('/foo/{bar:\w}/', '/foo/1/'),
            array('/foo/{bar:\w}/', '/foo/abc/'),
            array('/foo/{bar:\w}/', '/foo/123/'),
            array('/foo/{bar:\w}/', '/foo/123abc/'),

            array('/foo/{bar:[a-z]}/', '/foo/a/'),
            array('/foo/{bar:[a-z]}/', '/foo/abc/'),

            array('/foo/{bar:.}/', '/foo/1/'),
            array('/foo/{bar:.}/', '/foo/123/'),
            array('/foo/{bar:.}/', '/foo/a/'),
            array('/foo/{bar:.}/', '/foo/abc/'),
            array('/foo/{bar:.}/', '/foo/123abc/'),

            array('/foo/{bar:\d}/{yada:\w}/', '/foo/123/abc/'),

            array('/foo/{bar:\d}/({yada:\w}/)', '/foo/123/'),
            array('/foo/{bar:\d}/({yada:\w}/)', '/foo/123/abc'),
            array('/foo/{bar:\d}/({yada:\w}/)', '/foo/123/abc/'),
        );
    }

    /**
     * @dataProvider hostProvider
     */
    public function testMatchHost($host, $rHost)
    {
        $route = new Route('/foo/', 'some:controller');
        $route->host($host);
        $this->assertTrue($route->match($this->mockRequest('/foo/', null, null, $rHost)));
    }

    /**
     * @dataProvider hostProvider
     */
    public function testMatchHostFails($host, $rHost)
    {
        $route = new Route('/foo/', 'some:controller');
        $route->host('lorem.com');
        $this->assertFalse($route->match($this->mockRequest('/foo/', null, null, $rHost)));
    }

    public function hostProvider()
    {
        return array(
            array('localhost', 'localhost'),
            array('127.0.0.1', '127.0.0.1'),
            array('sub.domain.com', 'sub.domain.com'),
            array('sub.{basename}', 'sub.domain.com')
        );
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testMatchSchema($schema)
    {
        $route = new Route('/foo/', 'some:controller');
        $route->schema($schema);
        $this->assertTrue($route->match($this->mockRequest('/foo/', $schema)));
    }

    /**
     * @dataProvider schemaProvider
     */
    public function testMatchSchemaFails($schema)
    {
        $route = new Route('/foo/', 'some:controller');
        $route->schema('HTTP/1.2');
        $this->assertFalse($route->match($this->mockRequest('/foo/', $schema)));
    }

    public function schemaProvider()
    {
        return array(
            array('HTTP/1.0'),
            array('HTTP/1.1')
        );
    }

    /**
     * @dataProvider methodProvider
     */
    public function testMatchMethod($methods)
    {
        $route = new Route('/foo/', 'some:controller');
        $route->methods($methods);
        $this->assertTrue($route->match($this->mockRequest('/foo/', null, reset($methods))));
    }

    /**
     * @dataProvider methodProvider
     */
    public function testMatchMethodFails($methods)
    {
        $route = new Route('/foo/', 'some:controller');
        $route->methods('OPTION');
        $this->assertFalse($route->match($this->mockRequest('/foo/', null, reset($methods))));
    }

    public function methodProvider()
    {
        return array(
            array(array('GET')),
            array(array('POST')),
            array(array('PUT')),
            array(array('DELETE')),
            array(array('GET', 'POST')),
            array(array('PUT', 'DELETE')),
            array(array('GET', 'POST', 'PUT', 'DELETE')),
        );
    }

    /**
     * @dataProvider checkProvider
     */
    public function testCheck($pattern, $arguments)
    {
        $route = new Route($pattern, 'some:controller', $arguments);
        $this->assertTrue($route->check('some:controller', $arguments));
    }

    public function checkProvider()
    {
        return array(
            array('/foo/', array()),
            array('/foo/{bar:\d}/', array('bar' => 1)),
            array('/foo/{bar:\d}/', array('bar' => 123)),

            array('/foo/{bar:\w}/', array('bar' => 1)),
            array('/foo/{bar:\w}/', array('bar' => '123')),
            array('/foo/{bar:\w}/', array('bar' => 'a')),
            array('/foo/{bar:\w}/', array('bar' => 'abc')),
            array('/foo/{bar:\w}/', array('bar' => '123abc')),

            array('/foo/{bar:[a-z]}/', array('bar' => 'a')),
            array('/foo/{bar:[a-z]}/', array('bar' => 'abc')),

            array('/foo/{bar:.}/', array('bar' => 1)),
            array('/foo/{bar:.}/', array('bar' => '123')),
            array('/foo/{bar:.}/', array('bar' => 'a')),
            array('/foo/{bar:.}/', array('bar' => 'abc')),
            array('/foo/{bar:.}/', array('bar' => '123abc')),

            array('/foo/{bar:\d}/{yada:\w}/', array('bar' => '123', 'yada' => 'abc')),

            array('/foo/{bar:\d}/({yada:\w}/)', array('bar' => 123)),
            array('/foo/{bar:\d}/({yada:\w}/)', array('bar' => '123', 'yada' => 'abc'))
        );
    }

    protected function mockRequest($url, $schema = null, $method = null, $host = null)
    {
        $request = $this->getMock('moss\http\request\RequestInterface');
        $request
            ->expects($this->any())
            ->method('url')
            ->will($this->returnValue($url));

        $request
            ->expects($this->any())
            ->method('schema')
            ->will($this->returnValue($schema));

        $request
            ->expects($this->any())
            ->method('method')
            ->will($this->returnValue($method));

        $request
            ->expects($this->any())
            ->method('host')
            ->will($this->returnValue($host));

        return $request;
    }


}
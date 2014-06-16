<?php
namespace Moss\Http\Router;

class RouteTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider             patternQuantificationProvider
     * @expectedException \Moss\Http\router\RouteException
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
    public function testRequirementsFromRoute($pattern, $expected)
    {
        $route = new Route($pattern, 'some:controller');
        $this->assertEquals($expected, $route->requirements());
    }

    public function requirementsProvider()
    {
        return array(
            array('/foo/', array(), array()),
            array('/foo/{bar:\d}/', array('bar' => '\d+')),
            array('/foo/{bar:\d}/{yada:\w}/', array('bar' => '\d+', 'yada' => '\w+')),
            array('/foo/{bar:\d}/({yada:\w})', array('bar' => '\d+', 'yada' => '\w*')),
            array('/foo/{bar:\d}/({yada:\w})/', array('bar' => '\d+', 'yada' => '\w*')),
            array('/foo/{bar}/', array('bar' => '[a-z0-9-._]+'), array('bar' => '\w+')),
            array('/foo/{bar}/{yada}/', array('bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]+')),
            array('/foo/{bar}/({yada}/)', array('bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]*')),
            array('/foo/{bar}/({yada}/)', array('bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]*')),
            array('/foo/{bar}/{yada}.html', array('bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]+')),
            array('/foo/{bar}/({yada}.html)', array('bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]*')),
            array('/foo/{bar}/({yada}.html)', array('bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]*')),
        );
    }

    /**
     * @dataProvider argumentsProvider
     */
    public function testArgumentsFromRoute($pattern, $arg, $expected)
    {
        $route = new Route($pattern, 'some:controller', $arg);
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
            array('/foo/{bar:\d}/({yada:\w}/)', array(), array('bar' => null, 'yada' => null)),
            array('/foo/{bar:\d}/({yada:\w}/)', array('foo' => 1), array('foo' => 1, 'bar' => null, 'yada' => null)),
            array('/foo/{bar:\d}/{yada:\w}.html', array(), array('bar' => null, 'yada' => null)),
            array('/foo/{bar:\d}/{yada:\w}.html', array('foo' => 1), array('foo' => 1, 'bar' => null, 'yada' => null)),
            array('/foo/{bar:\d}/({yada:\w}.html)', array(), array('bar' => null, 'yada' => null)),
            array('/foo/{bar:\d}/({yada:\w}.html)', array('foo' => 1), array('foo' => 1, 'bar' => null, 'yada' => null)),
        );
    }

    /**
     * @dataProvider matchSchemaProvider
     */
    public function testMatchSchema($schema, $expected = null)
    {
        $route = new Route('/foo/', 'some:controller');
        $route->schema($expected);
        $this->assertTrue($route->match($this->mockRequest('/foo', $schema)));
    }

    public function matchSchemaProvider()
    {
        return array(
            array('http'),
            array('http'),
            array('http', 'http'),
            array('https', 'https'),
        );
    }

    /**
     * @dataProvider matchMethodProvider
     */
    public function testMatchMethod($method, $expected = null)
    {
        $route = new Route('/foo/', 'some:controller');
        $route->methods($expected);
        $this->assertTrue($route->match($this->mockRequest('/foo', null, $method)));
    }

    public function matchMethodProvider()
    {
        return array(
            array('GET', array()),
            array('POST', array('POST')),
            array('OPTIONS', array('OPTIONS')),
            array('HEAD', array('HEAD')),
            array('PUT', array('POST', 'PUT')),
            array('DELETE', array('POST', 'DELETE')),
            array('TRACE', array('TRACE')),
        );
    }

    /**
     * @dataProvider matchHostProvider
     */
    public function testMatchHost($host, $expected = null)
    {
        $route = new Route('/foo/', 'some:controller');
        $route->host($expected);
        $this->assertTrue($route->match($this->mockRequest('/foo', null, null, $host)));
    }

    public function matchHostProvider()
    {
        return array(
            array('foo.com'),
            array('foo.com', 'foo.com'),
            array('foo.bar.com', 'foo.bar.com'),
            array('localhost', 'localhost'),
            array('sub.localhost', 'sub.localhost'),
        );
    }

    /**
     * @dataProvider matchProvider
     */
    public function testMatchUrl($pattern, $path, $arguments = array())
    {
        $route = new Route($pattern, 'some:controller', $arguments);
        $route->match($this->mockRequest($path));
    }

    /**
     * @dataProvider matchProvider
     */
    public function testMatchUrlController($pattern, $path, $arguments = array())
    {
        $route = new Route($pattern, 'some:controller', $arguments);
        $route->match($this->mockRequest($path));
        $this->assertEquals('some:controller', $route->controller());
    }

    /**
     * @dataProvider matchProvider
     */
    public function testMatchUrlArguments($pattern, $path, $arguments = array(), $expectedArguments = array())
    {
        $route = new Route($pattern, 'some:controller', $arguments);
        $route->match($this->mockRequest($path));
        $this->assertEquals($expectedArguments, $route->arguments());
    }

    public function matchProvider()
    {
        return array(
            array(
                '/foo/',
                '/foo'
            ),
            array(
                '/foo/',
                '/foo/'
            ),
            array(
                '/foo/',
                '/foo',
                array('foo' => 123),
                array('foo' => 123)
            ),
            array(
                '/foo/',
                '/foo/',
                array('foo' => 123),
                array('foo' => 123)

            ),
            array(
                '/foo/{bar:\d}/',
                '/foo/1',
                array(),
                array('bar' => 1)
            ),
            array(
                '/foo/{bar:\d}/',
                '/foo/1/',
                array(),
                array('bar' => 1)
            ),
            array(
                '/foo/{bar:\d}/',
                '/foo/123',
                array(),
                array('bar' => 123)
            ),
            array(
                '/foo/{bar:\d}/',
                '/foo/123/',
                array(),
                array('bar' => 123)
            ),
            array(
                '/foo/{bar:\d}/{yada:\w}/',
                '/foo/1/a',
                array(),
                array('bar' => 1, 'yada' => 'a')
            ),
            array(
                '/foo/{bar:\d}/{yada:\w}/',
                '/foo/1/a/',
                array(),
                array('bar' => 1, 'yada' => 'a')
            ),
            array(
                '/foo/{bar:\d}/{yada:\w}/',
                '/foo/123/abc',
                array(),
                array('bar' => 123, 'yada' => 'abc')
            ),
            array(
                '/foo/{bar:\d}/{yada:\w}/',
                '/foo/123/abc/',
                array(),
                array('bar' => 123, 'yada' => 'abc')
            ),
            array(
                '/foo/{bar:\d}/({yada:\w}/)',
                '/foo/1/',
                array(),
                array('bar' => 1, 'yada' => null)
            ),
            array(
                '/foo/{bar:\d}/({yada:\w}/)',
                '/foo/123/abc',
                array(),
                array('bar' => 123, 'yada' => 'abc')
            ),
            array(
                '/foo/{bar:\d}/({yada:\w}/)',
                '/foo/123/abc/',
                array(),
                array('bar' => 123, 'yada' => 'abc')
            ),
            array(
                '/foo/{bar:\d}/{yada:\w}.html',
                '/foo/1/a.html',
                array(),
                array('bar' => 1, 'yada' => 'a')
            ),
            array(
                '/foo/{bar:\d}/{yada:\w}.html',
                '/foo/1/a.html',
                array(),
                array('bar' => 1, 'yada' => 'a')
            ),
            array(
                '/foo/{bar:\d}/{yada:\w}.html',
                '/foo/123/abc.html',
                array(),
                array('bar' => 123, 'yada' => 'abc')
            ),
            array(
                '/foo/{bar:\d}/{yada:\w}.html',
                '/foo/123/abc.html',
                array(),
                array('bar' => 123, 'yada' => 'abc')
            ),
            array(
                '/foo/{bar:\d}/({yada:\w}.html)',
                '/foo/1/',
                array(),
                array('bar' => 1, 'yada' => null)
            ),
            array(
                '/foo/{bar:\d}/({yada:\w}.html)',
                '/foo/123/abc.html',
                array(),
                array('bar' => 123, 'yada' => 'abc')
            ),
            array(
                '/foo/{bar:\d}/({yada:\w}.html)',
                '/foo/123/abc.html',
                array(),
                array('bar' => 123, 'yada' => 'abc')
            ),
        );
    }

    /**
     * @dataProvider checkProvider
     */
    public function testCheck($pattern, $required, $arguments)
    {
        $route = new Route($pattern, 'some:controller', $required);
        $this->assertTrue($route->check('some:controller', $arguments));
    }

    public function checkProvider()
    {
        return array(
            array(
                '/foo',
                array(),
                array()
            ),
            array(
                '/foo',
                array('foo' => 'foo'),
                array('foo' => 'foo')
            ),
            array(
                '/foo',
                array('bar' => 'bar'),
                array('bar' => 'bar')
            ),
            array(
                '/foo/',
                array(),
                array()
            ),
            array(
                '/foo/{bar:\d}',
                array(),
                array('bar' => 123)
            ),
            array(
                '/foo/{bar:\d}/',
                array(),
                array('bar' => 123)
            ),
            array(
                '/foo/{bar:\d}/{yada:\w}',
                array(),
                array('bar' => 123, 'yada' => 'abc')
            ),
            array(
                '/foo/{bar:\d}/{yada:\w}/',
                array(),
                array('bar' => 123, 'yada' => 'abc')
            ),
            array(
                '/foo/{bar:\d}/({yada:\w})',
                array(),
                array('bar' => 123, 'yada' => 'abc')
            ),
            array(
                '/foo/{bar:\d}/({yada:\w})/',
                array(),
                array('bar' => 123, 'yada' => 'abc')
            ),
            array(
                '/foo/{bar:\d}/({yada:\w}.html)',
                array(),
                array('bar' => 123, 'yada' => 'abc')
            ),
        );
    }

    /**
     * @dataProvider failCheckProvider
     */
    public function testCheckFailsByController($pattern, $required, $arguments)
    {
        $route = new Route($pattern, 'some:controller', $required);
        $this->assertFalse($route->check('different:controller', $arguments));
    }

    /**
     * @dataProvider failCheckProvider
     */
    public function testCheckFailsByArguments($pattern, $required, $arguments)
    {
        $route = new Route($pattern, 'some:controller', $required);
        $this->assertFalse($route->check('some:controller', $arguments));
    }

    public function failCheckProvider()
    {
        return array(
            array(
                '/foo',
                array('foo' => 'foo'),
                array()
            ),
            array(
                '/foo',
                array('bar' => 'bar'),
                array()
            ),
            array(
                '/foo/{bar:\d}',
                array(),
                array()
            ),
            array(
                '/foo/{bar:\d}/',
                array(),
                array()
            ),
            array(
                '/foo/{bar:\d}/{yada:\w}',
                array(),
                array('bar' => 123)
            ),
            array(
                '/foo/{bar:\d}/{yada:\w}/',
                array(),
                array('bar' => 123)
            ),
            array(
                '/foo/{bar:\d}/({yada:\w})',
                array(),
                array('yada' => 'abc')
            ),
            array(
                '/foo/{bar:\d}/({yada:\w})/',
                array(),
                array('yada' => 'abc')
            ),
            array(
                '/foo/{bar:\d}/({yada:\w}.html)',
                array(),
                array('yada' => 'abc')
            ),
        );
    }

    /**
     * @dataProvider makeProvider
     */
    public function testMakeWithHost($uri, $pattern, $arguments = array())
    {
        $route = new Route($pattern, 'some:controller', $arguments);
        $this->assertEquals('http://host.com' . $uri, $route->make('http://host.com', $arguments));
    }

    public function makeProvider()
    {
        return array(
            array(
                '/foo/',
                '/foo/'
            ),
            array(
                '/foo/',
                '/foo/',
                array('foo' => 123)
            ),
            array(
                '/foo/1/',
                '/foo/{bar:\d}/',
                array('bar' => 1)
            ),
            array(
                '/foo/123/',
                '/foo/{bar:\d}/',
                array('bar' => 123)
            ),
            array(
                '/foo/1/a/',
                '/foo/{bar:\d}/{yada:\w}/',
                array('bar' => 1, 'yada' => 'a')
            ),
            array(
                '/foo/123/abc/',
                '/foo/{bar:\d}/{yada:\w}/',
                array('bar' => 123, 'yada' => 'abc')
            ),
            array(
                '/foo/1/',
                '/foo/{bar:\d}/({yada:\w}/)',
                array('bar' => 1)
            ),
            array(
                '/foo/123/abc/',
                '/foo/{bar:\d}/({yada:\w}/)',
                array('bar' => 123, 'yada' => 'abc')
            ),
            array(
                '/foo/1/a.html',
                '/foo/{bar:\d}/{yada:\w}.html',
                array('bar' => 1, 'yada' => 'a')
            ),
            array(
                '/foo/123/abc.html',
                '/foo/{bar:\d}/{yada:\w}.html',
                array('bar' => 123, 'yada' => 'abc')
            ),
            array(
                '/foo/1/',
                '/foo/{bar:\d}/({yada:\w}.html)',
                array('bar' => 1)
            ),
            array(
                '/foo/123/abc.html',
                '/foo/{bar:\d}/({yada:\w}.html)',
                array('bar' => 123, 'yada' => 'abc')
            ),
        );
    }

    /**
     * @dataProvider hostProvider
     */
    public function testMakeWithHostSubDomain($schema, $host)
    {
        $route = new Route('/foo/', 'some:controller');
        $route->host('sub.{basename}');
        $this->assertEquals($schema . '://sub.' . $host . '/foo/', $route->make($schema.'://'.$host));
    }

    public function hostProvider()
    {
        return array(
            array('http', 'host.com'),
            array('https', 'host.com'),
            array('http', 'ver.sub.domain.com'),
        );
    }

    public function testMakeWithoutSchema()
    {
        $route = new Route('/foo/', 'some:controller');
        $route->host('sub.{basename}');
        $this->assertEquals('http://sub.host.com/foo/', $route->make('host.com'));
    }

    public function testMakeHTTPSWithFromHTTPHost()
    {
        $route = new Route('/foo/', 'some:controller');
        $route->schema('https');
        $this->assertEquals('https://host.com/foo/', $route->make('http://host.com'));
    }

    public function testMakeWhenInsideSubdirectory()
    {
        $route = new Route('/foo/', 'some:controller');
        $route->schema('https');
        $this->assertEquals('https://host.com/dir/foo/', $route->make('http://host.com/dir/'));
    }

    protected function mockRequest($path, $schema = null, $method = null, $host = null)
    {
        $request = $this->getMock('Moss\Http\request\RequestInterface');
        $request
            ->expects($this->any())
            ->method('path')
            ->will($this->returnValue($path));

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
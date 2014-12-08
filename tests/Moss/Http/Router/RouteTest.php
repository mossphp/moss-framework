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
        return [
            ['/foo/{bar:.?}/'],
            ['/foo/{bar:.*}/'],
            ['/foo/{bar:.+}/'],
        ];
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
        return [
            ['/foo/', [], []],
            ['/foo/{bar:\d}/', ['bar' => '\d+']],
            ['/foo/{bar:\d}/{yada:\w}/', ['bar' => '\d+', 'yada' => '\w+']],
            ['/foo/{bar:\d}/({yada:\w})', ['bar' => '\d+', 'yada' => '\w*']],
            ['/foo/{bar:\d}/({yada:\w})/', ['bar' => '\d+', 'yada' => '\w*']],
            ['/foo/{bar}/', ['bar' => '[a-z0-9-._]+'], ['bar' => '\w+']],
            ['/foo/{bar}/{yada}/', ['bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]+']],
            ['/foo/{bar}/({yada}/)', ['bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]*']],
            ['/foo/{bar}/({yada}/)', ['bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]*']],
            ['/foo/{bar}/{yada}.html', ['bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]+']],
            ['/foo/{bar}/({yada}.html)', ['bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]*']],
            ['/foo/{bar}/({yada}.html)', ['bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]*']],
        ];
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
        return [
            ['/foo/', [], []],
            ['/foo/{bar:\d}/', [], ['bar' => null]],
            ['/foo/{bar:\d}/', ['foo' => 1], ['foo' => 1, 'bar' => null]],
            ['/foo/{bar:\d}/{yada:\w}/', [], ['bar' => null, 'yada' => null]],
            ['/foo/{bar:\d}/{yada:\w}/', ['foo' => 1], ['foo' => 1, 'bar' => null, 'yada' => null]],
            ['/foo/{bar:\d}/({yada:\w}/)', [], ['bar' => null, 'yada' => null]],
            ['/foo/{bar:\d}/({yada:\w}/)', ['foo' => 1], ['foo' => 1, 'bar' => null, 'yada' => null]],
            ['/foo/{bar:\d}/{yada:\w}.html', [], ['bar' => null, 'yada' => null]],
            ['/foo/{bar:\d}/{yada:\w}.html', ['foo' => 1], ['foo' => 1, 'bar' => null, 'yada' => null]],
            ['/foo/{bar:\d}/({yada:\w}.html)', [], ['bar' => null, 'yada' => null]],
            ['/foo/{bar:\d}/({yada:\w}.html)', ['foo' => 1], ['foo' => 1, 'bar' => null, 'yada' => null]],
        ];
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
        return [
            ['http'],
            ['http'],
            ['http', 'http'],
            ['https', 'https'],
        ];
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
        return [
            ['GET', []],
            ['POST', ['POST']],
            ['OPTIONS', ['OPTIONS']],
            ['HEAD', ['HEAD']],
            ['PUT', ['POST', 'PUT']],
            ['DELETE', ['POST', 'DELETE']],
            ['TRACE', ['TRACE']],
        ];
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
        return [
            ['foo.com'],
            ['foo.com', 'foo.com'],
            ['foo.bar.com', 'foo.bar.com'],
            ['localhost', 'localhost'],
            ['sub.localhost', 'sub.localhost'],
        ];
    }

    /**
     * @dataProvider matchProvider
     */
    public function testMatchUrl($pattern, $path, $arguments = [])
    {
        $route = new Route($pattern, 'some:controller', $arguments);
        $route->match($this->mockRequest($path));
    }

    /**
     * @dataProvider matchProvider
     */
    public function testMatchUrlController($pattern, $path, $arguments = [])
    {
        $route = new Route($pattern, 'some:controller', $arguments);
        $route->match($this->mockRequest($path));
        $this->assertEquals('some:controller', $route->controller());
    }

    /**
     * @dataProvider matchProvider
     */
    public function testMatchUrlArguments($pattern, $path, $arguments = [], $expectedArguments = [])
    {
        $route = new Route($pattern, 'some:controller', $arguments);
        $route->match($this->mockRequest($path));
        $this->assertEquals($expectedArguments, $route->arguments());
    }

    public function matchProvider()
    {
        return [
            [
                '/foo/',
                '/foo'
            ],
            [
                '/foo/',
                '/foo/'
            ],
            [
                '/foo/',
                '/foo',
                ['foo' => 123],
                ['foo' => 123]
            ],
            [
                '/foo/',
                '/foo/',
                ['foo' => 123],
                ['foo' => 123]

            ],
            [
                '/foo/{bar:\d}/',
                '/foo/1',
                [],
                ['bar' => 1]
            ],
            [
                '/foo/{bar:\d}/',
                '/foo/1/',
                [],
                ['bar' => 1]
            ],
            [
                '/foo/{bar:\d}/',
                '/foo/123',
                [],
                ['bar' => 123]
            ],
            [
                '/foo/{bar:\d}/',
                '/foo/123/',
                [],
                ['bar' => 123]
            ],
            [
                '/foo/{bar:\d}/{yada:\w}/',
                '/foo/1/a',
                [],
                ['bar' => 1, 'yada' => 'a']
            ],
            [
                '/foo/{bar:\d}/{yada:\w}/',
                '/foo/1/a/',
                [],
                ['bar' => 1, 'yada' => 'a']
            ],
            [
                '/foo/{bar:\d}/{yada:\w}/',
                '/foo/123/abc',
                [],
                ['bar' => 123, 'yada' => 'abc']
            ],
            [
                '/foo/{bar:\d}/{yada:\w}/',
                '/foo/123/abc/',
                [],
                ['bar' => 123, 'yada' => 'abc']
            ],
            [
                '/foo/{bar:\d}/({yada:\w}/)',
                '/foo/1/',
                [],
                ['bar' => 1, 'yada' => null]
            ],
            [
                '/foo/{bar:\d}/({yada:\w}/)',
                '/foo/123/abc',
                [],
                ['bar' => 123, 'yada' => 'abc']
            ],
            [
                '/foo/{bar:\d}/({yada:\w}/)',
                '/foo/123/abc/',
                [],
                ['bar' => 123, 'yada' => 'abc']
            ],
            [
                '/foo/{bar:\d}/{yada:\w}.html',
                '/foo/1/a.html',
                [],
                ['bar' => 1, 'yada' => 'a']
            ],
            [
                '/foo/{bar:\d}/{yada:\w}.html',
                '/foo/1/a.html',
                [],
                ['bar' => 1, 'yada' => 'a']
            ],
            [
                '/foo/{bar:\d}/{yada:\w}.html',
                '/foo/123/abc.html',
                [],
                ['bar' => 123, 'yada' => 'abc']
            ],
            [
                '/foo/{bar:\d}/{yada:\w}.html',
                '/foo/123/abc.html',
                [],
                ['bar' => 123, 'yada' => 'abc']
            ],
            [
                '/foo/{bar:\d}/({yada:\w}.html)',
                '/foo/1/',
                [],
                ['bar' => 1, 'yada' => null]
            ],
            [
                '/foo/{bar:\d}/({yada:\w}.html)',
                '/foo/123/abc.html',
                [],
                ['bar' => 123, 'yada' => 'abc']
            ],
            [
                '/foo/{bar:\d}/({yada:\w}.html)',
                '/foo/123/abc.html',
                [],
                ['bar' => 123, 'yada' => 'abc']
            ],
        ];
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
        return [
            [
                '/foo',
                [],
                []
            ],
            [
                '/foo',
                ['foo' => 'foo'],
                ['foo' => 'foo']
            ],
            [
                '/foo',
                ['bar' => 'bar'],
                ['bar' => 'bar']
            ],
            [
                '/foo/',
                [],
                []
            ],
            [
                '/foo/{bar:\d}',
                [],
                ['bar' => 123]
            ],
            [
                '/foo/{bar:\d}/',
                [],
                ['bar' => 123]
            ],
            [
                '/foo/{bar:\d}/{yada:\w}',
                [],
                ['bar' => 123, 'yada' => 'abc']
            ],
            [
                '/foo/{bar:\d}/{yada:\w}/',
                [],
                ['bar' => 123, 'yada' => 'abc']
            ],
            [
                '/foo/{bar:\d}/({yada:\w}]',
                [],
                ['bar' => 123, 'yada' => 'abc']
            ],
            [
                '/foo/{bar:\d}/({yada:\w}]/',
                [],
                ['bar' => 123, 'yada' => 'abc']
            ],
            [
                '/foo/{bar:\d}/({yada:\w}.html]',
                [],
                ['bar' => 123, 'yada' => 'abc']
            ],
        ];
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
        return [
            [
                '/foo',
                ['foo' => 'foo'],
                []
            ],
            [
                '/foo',
                ['bar' => 'bar'],
                []
            ],
            [
                '/foo/{bar:\d}',
                [],
                []
            ],
            [
                '/foo/{bar:\d}/',
                [],
                []
            ],
            [
                '/foo/{bar:\d}/{yada:\w}',
                [],
                ['bar' => 123]
            ],
            [
                '/foo/{bar:\d}/{yada:\w}/',
                [],
                ['bar' => 123]
            ],
            [
                '/foo/{bar:\d}/({yada:\w}]',
                [],
                ['yada' => 'abc']
            ],
            [
                '/foo/{bar:\d}/({yada:\w}]/',
                [],
                ['yada' => 'abc']
            ],
            [
                '/foo/{bar:\d}/({yada:\w}.html]',
                [],
                ['yada' => 'abc']
            ],
        ];
    }

    /**
     * @dataProvider makeProvider
     */
    public function testMakeWithHost($uri, $pattern, $arguments = [])
    {
        $route = new Route($pattern, 'some:controller', $arguments);
        $this->assertEquals('http://host.com' . $uri, $route->make('http://host.com', $arguments));
    }

    public function makeProvider()
    {
        return [
            [
                '/foo/',
                '/foo/'
            ],
            [
                '/foo/',
                '/foo/',
                ['foo' => 123]
            ],
            [
                '/foo/1/',
                '/foo/{bar:\d}/',
                ['bar' => 1]
            ],
            [
                '/foo/123/',
                '/foo/{bar:\d}/',
                ['bar' => 123]
            ],
            [
                '/foo/1/a/',
                '/foo/{bar:\d}/{yada:\w}/',
                ['bar' => 1, 'yada' => 'a']
            ],
            [
                '/foo/123/abc/',
                '/foo/{bar:\d}/{yada:\w}/',
                ['bar' => 123, 'yada' => 'abc']
            ],
            [
                '/foo/1/',
                '/foo/{bar:\d}/({yada:\w}/)',
                ['bar' => 1]
            ],
            [
                '/foo/123/abc/',
                '/foo/{bar:\d}/({yada:\w}/)',
                ['bar' => 123, 'yada' => 'abc']
            ],
            [
                '/foo/1/a.html',
                '/foo/{bar:\d}/{yada:\w}.html',
                ['bar' => 1, 'yada' => 'a']
            ],
            [
                '/foo/123/abc.html',
                '/foo/{bar:\d}/{yada:\w}.html',
                ['bar' => 123, 'yada' => 'abc']
            ],
            [
                '/foo/1/',
                '/foo/{bar:\d}/({yada:\w}.html]',
                ['bar' => 1]
            ],
            [
                '/foo/123/abc.html',
                '/foo/{bar:\d}/({yada:\w}.html)',
                ['bar' => 123, 'yada' => 'abc']
            ],
        ];
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
        return [
            ['http', 'host.com'],
            ['https', 'host.com'],
            ['http', 'ver.sub.domain.com'],
        ];
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

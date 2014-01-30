<?php
namespace moss\http\router;

class RouteTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider             patternQuantificationProvider
     * @expectedException \moss\http\router\RouteException
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
    public function testRequirementsFromRoute($pattern, $req, $expected)
    {
        $route = new Route($pattern, 'some:controller');
        $this->assertEquals($req, $route->requirements());
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
            array('/foo/{bar}/{yada}.html', array('bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]+'), array('bar' => '\w+', 'yada' => '\d+')),
            array('/foo/{bar}/({yada}.html)', array('bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]*(\.html)?'), array('bar' => '\w+', 'yada' => '\d*(\.html)?')),
            array('/foo/{bar}/({yada}.html)', array('bar' => '[a-z0-9-._]+', 'yada' => '[a-z0-9-._]*(\.html)?'), array('bar' => '\w+', 'yada' => '\d+')),
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
            array('/foo/{bar:\d}/({yada:\w}/)', array(), array('bar' => null)),
            array('/foo/{bar:\d}/({yada:\w}/)', array('foo' => 1), array('foo' => 1, 'bar' => null)),
            array('/foo/{bar:\d}/{yada:\w}.html', array(), array('bar' => null, 'yada' => null)),
            array('/foo/{bar:\d}/{yada:\w}.html', array('foo' => 1), array('foo' => 1, 'bar' => null, 'yada' => null)),
            array('/foo/{bar:\d}/({yada:\w}.html)', array(), array('bar' => null)),
            array('/foo/{bar:\d}/({yada:\w.html)', array('foo' => 1), array('foo' => 1, 'bar' => null)),
        );
    }

    /**
     * @dataProvider matchProvider
     */
    public function testMatchUrl($pattern, $arg = array(array(), array()), $host = array(null, null), $schema = array(null, null), $methods = array(array(), null))
    {
        $route = new Route($pattern[0], 'some:controller', $arg[0]);
        $route->host($host[0]);
        $route->schema($schema[0]);
        $route->methods($methods[0]);
        $this->assertTrue($route->match($this->mockRequest($pattern[1], $schema[1], $methods[1], $host[1])), 'Path mismatch');
        $this->assertEquals('some:controller', $route->controller(), 'Controller not equal');
        $this->assertEquals($arg[1], $route->arguments(), 'Arguments not equal');
    }

    public function matchProvider()
    {
        return array(
            array(
                array('/foo/', '/foo')
            ),
            array(
                array('/foo/', '/foo/')
            ),
            array(
                array('/foo/', '/foo'),
                array(array('foo' => 123), array('foo' => 123))
            ),
            array(
                array('/foo/', '/foo/'),
                array(array('foo' => 123), array('foo' => 123)),
            ),
            array(
                array('/foo/{bar:\d}/', '/foo/1'),
                array(array(), array('bar' => 1)),
            ),
            array(
                array('/foo/{bar:\d}/', '/foo/1/'),
                array(array(), array('bar' => 1)),
            ),
            array(
                array('/foo/{bar:\d}/', '/foo/123'),
                array(array(), array('bar' => 123)),
            ),
            array(
                array('/foo/{bar:\d}/', '/foo/123/'),
                array(array(), array('bar' => 123)),
            ),
            array(
                array('/foo/{bar:\d}/{yada:\w}/', '/foo/1/a'),
                array(array(), array('bar' => 1, 'yada' => 'a')),
            ),
            array(
                array('/foo/{bar:\d}/{yada:\w}/', '/foo/1/a/'),
                array(array(), array('bar' => 1, 'yada' => 'a')),
            ),
            array(
                array('/foo/{bar:\d}/{yada:\w}/', '/foo/123/abc'),
                array(array(), array('bar' => 123, 'yada' => 'abc')),
            ),
            array(
                array('/foo/{bar:\d}/{yada:\w}/', '/foo/123/abc/'),
                array(array(), array('bar' => 123, 'yada' => 'abc')),
            ),
            array(
                array('/foo/{bar:\d}/({yada:\w}/)', '/foo/1/'),
                array(array(), array('bar' => 1, 'yada' => null)),
            ),
            array(
                array('/foo/{bar:\d}/({yada:\w}/)', '/foo/123/abc'),
                array(array(), array('bar' => 123, 'yada' => 'abc')),
            ),
            array(
                array('/foo/{bar:\d}/({yada:\w}/)', '/foo/123/abc/'),
                array(array(), array('bar' => 123, 'yada' => 'abc')),
            ),
            array(
                array('/foo/{bar:\d}/{yada:\w}.html', '/foo/1/a.html'),
                array(array(), array('bar' => 1, 'yada' => 'a')),
            ),
            array(
                array('/foo/{bar:\d}/{yada:\w}.html', '/foo/1/a.html'),
                array(array(), array('bar' => 1, 'yada' => 'a')),
            ),
            array(
                array('/foo/{bar:\d}/{yada:\w}.html', '/foo/123/abc.html'),
                array(array(), array('bar' => 123, 'yada' => 'abc')),
            ),
            array(
                array('/foo/{bar:\d}/{yada:\w}.html', '/foo/123/abc.html'),
                array(array(), array('bar' => 123, 'yada' => 'abc')),
            ),
            array(
                array('/foo/{bar:\d}/({yada:\w}.html)', '/foo/1/'),
                array(array(), array('bar' => 1, 'yada' => null)),
            ),
            array(
                array('/foo/{bar:\d}/({yada:\w}.html)', '/foo/123/abc.html'),
                array(array(), array('bar' => 123, 'yada' => 'abc')),
            ),
            array(
                array('/foo/{bar:\d}/({yada:\w}.html)', '/foo/123/abc.html'),
                array(array(), array('bar' => 123, 'yada' => 'abc')),
            ),
        );
    }

    /**
     * @dataProvider checkProvider
     */
    public function testCheck($path, $pattern, $arguments)
    {
        $route = new Route($pattern, 'some:controller', $arguments);
        $this->assertTrue($route->match($this->mockRequest($path)));
    }

    public function checkProvider()
    {
        return array(
            array('/foo', '/foo/', array()),
            array('/foo/', '/foo/', array()),
            array('/foo/123', '/foo/{bar:\d}/', array()),
            array('/foo/123/', '/foo/{bar:\d}/', array()),
            array('/foo/123/bar', '/foo/{bar:\d}/{yada:\w}/', array()),
            array('/foo/123/bar/', '/foo/{bar:\d}/{yada:\w}/', array()),
            array('/foo/123', '/foo/{bar:\d}/({yada:\w}/)', array()),
            array('/foo/123/', '/foo/{bar:\d}/({yada:\w}/)', array()),
            array('/foo/123/bar', '/foo/{bar:\d}/({yada:\w}/)', array()),
            array('/foo/123/bar/', '/foo/{bar:\d}/({yada:\w}/)', array()),
            array('/foo/123', '/foo/{bar:\d}/({yada:\w}.html)', array()),
            array('/foo/123/', '/foo/{bar:\d}/({yada:\w}.html)', array()),
            array('/foo/123/bar.html', '/foo/{bar:\d}/({yada:\w}.html)', array()),
        );
    }

    /**
     * @dataProvider makeProvider
     */
    public function testMakeWithHost($uri, $pattern, $arguments = array())
    {
        $route = new Route($pattern, 'some:controller', $arguments);
        $this->assertEquals('http://host.com/'.ltrim($uri, '/'), $route->make('http://host.com/', $arguments));
    }

    /**
     * @dataProvider makeProvider
     */
    public function testMakeWithoutHost($uri, $pattern, $arguments = array())
    {
        $route = new Route($pattern, 'some:controller', $arguments);
        $this->assertEquals('./'.ltrim($uri, '/'), $route->make(null, $arguments));
    }

    public function makeProvider()
    {
        return array(
            array('/foo/', '/foo/'),
            array('/foo/', '/foo/', array('foo' => 123)),
            array('/foo/1/', '/foo/{bar:\d}/', array('bar' => 1)),
            array('/foo/123/', '/foo/{bar:\d}/', array('bar' => 123)),
            array('/foo/1/a/', '/foo/{bar:\d}/{yada:\w}/', array('bar' => 1, 'yada' => 'a')),
            array('/foo/123/abc/', '/foo/{bar:\d}/{yada:\w}/', array('bar' => 123, 'yada' => 'abc')),
            array('/foo/1/', '/foo/{bar:\d}/({yada:\w}/)', array('bar' => 1)),
            array('/foo/123/abc/', '/foo/{bar:\d}/({yada:\w}/)', array('bar' => 123, 'yada' => 'abc')),
            array('/foo/1/a.html', '/foo/{bar:\d}/{yada:\w}.html', array('bar' => 1, 'yada' => 'a')),
            array('/foo/123/abc.html', '/foo/{bar:\d}/{yada:\w}.html', array('bar' => 123, 'yada' => 'abc')),
            array('/foo/1/', '/foo/{bar:\d}/({yada:\w}.html)', array('bar' => 1)),
            array('/foo/123/abc.html', '/foo/{bar:\d}/({yada:\w}.html)', array('bar' => 123, 'yada' => 'abc')),
        );
    }

    protected function mockRequest($path, $schema = null, $method = null, $host = null)
    {
        $request = $this->getMock('moss\http\request\RequestInterface');
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
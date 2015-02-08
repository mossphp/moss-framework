<?php
namespace Moss\Http\Router;

class DynamicRouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider argumentsProvider
     */
    public function testControllerFromRequest($arguments, $expected)
    {
        $route = new DynamicRoute(
            '/foo/{controller}/({action}.html)',
            '\Some\Namespace\{controller}Controller@{action}Action'
        );
        $route->arguments($arguments);
        $this->assertEquals($expected, $route->controller());
    }

    public function argumentsProvider()
    {
        return [
            [['controller' => 'Foo', 'action' => 'yada'], '\\Some\\Namespace\\FooController@yadaAction'],
            [['controller' => 'Foo_Bar', 'action' => 'yada'], '\\Some\\Namespace\\Foo\\BarController@yadaAction'],
            [['controller' => 'foo_bar', 'action' => 'yada'], '\\Some\\Namespace\\foo\\barController@yadaAction'],
            [['controller' => 'Foo'], '\\Some\\Namespace\\FooController@indexAction']
        ];
    }

    /**
     * @dataProvider makeProvider
     */
    public function testUrlFromController($arguments, $expected)
    {
        $route = new DynamicRoute(
            '/foo/{controller}/({action}.html)',
            '\Some\Namespace\{controller}Controller@{action}Action'
        );

        $this->assertEquals($expected, $route->make('http://host.com', $arguments));
    }

    public function makeProvider()
    {
        return [
            [['controller' => 'Foo', 'action' => 'yada'], 'http://host.com/foo/Foo/yada.html'],
            [['controller' => 'Foo\\Bar', 'action' => 'yada'], 'http://host.com/foo/Foo_Bar/yada.html'],
            [['controller' => 'Foo_Bar', 'action' => 'yada'], 'http://host.com/foo/Foo_Bar/yada.html'],
            [['controller' => 'foo_bar', 'action' => 'yada'], 'http://host.com/foo/foo_bar/yada.html'],
            [['controller' => 'Foo'], 'http://host.com/foo/Foo/']
        ];
    }
}

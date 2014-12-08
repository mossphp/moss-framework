<?php

/*
* This file is part of the moss-framework package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Moss\Kernel\Factory;


class RouterFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $config = [
            'foo' => [
                'pattern' => '/',
                'controller' => 'foo',
                'arguments' => [],
                'methods' => []
            ]
        ];

        $factory = new RouterFactory();
        $result = $factory->build($config);

        $this->assertInstanceOf('\Moss\Http\Router\Router', $result);
    }

    public function applyDefaults()
    {
        $expected = [
            'pattern' => '/',
            'controller' => 'foo',
            'arguments' => [],
            'methods' => []
        ];

        $factory = new RouterFactory();
        $result = $factory->applyDefaults(['pattern' => '/', 'controller' => 'foo']);

        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Missing required "pattern" key in route definition
     */
    public function testPatternKeyMissing()
    {
        $definition = [
            'controller' => 'foo',
            'arguments' => [],
            'methods' => []
        ];

        $factory = new RouterFactory();
        $factory->applyDefaults($definition);
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Missing required "controller" key in route definition
     */
    public function testControllerKeyMissing()
    {
        $definition = [
            'pattern' => '/',
            'arguments' => [],
            'methods' => []
        ];

        $factory = new RouterFactory();
        $factory->applyDefaults($definition);
    }

    public function testDefinition()
    {
        $definition = [
            'pattern' => '/',
            'controller' => 'foo',
            'arguments' => [],
            'methods' => []
        ];

        $factory = new RouterFactory();
        $result = $factory->createDefinition($definition);

        $this->assertInstanceOf('\Moss\Http\Router\RouteInterface', $result);
    }
}

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
        $config = array(
            'foo' => array(
                'pattern' => '/',
                'controller' => 'foo',
                'arguments' => array(),
                'methods' => array()
            )
        );

        $factory = new RouterFactory();
        $result = $factory->build($config);

        $this->assertInstanceOf('\Moss\Http\Router\Router', $result);
    }

    public function applyDefaults()
    {
        $expected = array(
            'pattern' => '/',
            'controller' => 'foo',
            'arguments' => array(),
            'methods' => array()
        );

        $factory = new RouterFactory();
        $result = $factory->applyDefaults(array('pattern' => '/', 'controller' => 'foo'));

        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Missing required "pattern" key in route definition
     */
    public function testPatternKeyMissing()
    {
        $definition = array(
            'controller' => 'foo',
            'arguments' => array(),
            'methods' => array()
        );

        $factory = new RouterFactory();
        $factory->applyDefaults($definition);
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Missing required "controller" key in route definition
     */
    public function testControllerKeyMissing()
    {
        $definition = array(
            'pattern' => '/',
            'arguments' => array(),
            'methods' => array()
        );

        $factory = new RouterFactory();
        $factory->applyDefaults($definition);
    }

    public function testDefinition()
    {
        $definition = array(
            'pattern' => '/',
            'controller' => 'foo',
            'arguments' => array(),
            'methods' => array()
        );

        $factory = new RouterFactory();
        $result = $factory->createDefinition($definition);

        $this->assertInstanceOf('\Moss\Http\Router\RouteInterface', $result);
    }
}

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


class DispatcherFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $definition = array(
            'for' => function () { },
            'bar' => array(
                'component' => 'bar',
                'method' => null,
                'arguments' => array()
            )
        );

        $factory = new DispatcherFactory();
        $result = $factory->build($definition);

        $this->assertInstanceOf('\Moss\Dispatcher\DispatcherInterface', $result);
    }

    public function testApplyDefaults()
    {
        $expected = array(
            'component' => 'foo',
            'method' => 'bar',
            'arguments' => array()
        );

        $factory = new DispatcherFactory();
        $result = $factory->applyDefaults(array('component' => 'foo', 'method' => 'bar'));

        $this->assertEquals($expected, $result);
    }

    public function testDoNotApplyDefaultsToCallable()
    {
        $expected = function() {};

        $factory = new DispatcherFactory();
        $result = $factory->applyDefaults($expected);

        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Missing required "component" key in listener definition
     */
    public function testComponentKeyMissing()
    {
        $factory = new DispatcherFactory();
        $factory->applyDefaults(array());
    }

    public function testCallableDefinition()
    {
        $expected = function () { };

        $factory = new DispatcherFactory();
        $result = $factory->buildDefinition($expected);

        $this->assertEquals($expected, $result);
    }

    public function testListenerDefinition()
    {
        $definition = array(
            'component' => 'foo',
            'method' => null,
            'arguments' => array()
        );

        $factory = new DispatcherFactory();
        $result = $factory->buildDefinition($definition);

        $this->assertInstanceOf('\Moss\Dispatcher\ListenerInterface', $result);
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Invalid listener format, must be callable or array with component key
     */
    public function testInvalidListenerFormat()
    {
        $factory = new DispatcherFactory();
        $factory->buildDefinition('foo');
    }
}

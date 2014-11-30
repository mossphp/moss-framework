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


class ContainerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $config = array(
            'foo' => 'bar',
            'yada' => array('y', 'a', 'd', 'a'),
            'function' => array(
                'component' => function () { },
            ),
            'array' => array(
                'component' => array(
                    'class' => '\stdClass',
                    'arguments' => array(),
                    'calls' => array('method' => array('arg'))
                ),
                'shared' => false
            )
        );

        $factory = new ContainerFactory();
        $result = $factory->build($config);

        $this->assertInstanceOf('\Moss\Container\ContainerInterface', $result);
    }

    public function testDoNotApplyDefaultsToScalar()
    {
        $expected = array(
            'foo' => 'bar'
        );

        $factory = new ContainerFactory();
        $result = $factory->applyDefaults($expected);

        $this->assertEquals($expected, $result);
    }

    public function testDoNotApplyDefaultsToCallable()
    {
        $expected = array(
            'component' => function () { },
            'shared' => false
        );

        $factory = new ContainerFactory();
        $result = $factory->applyDefaults($expected);

        $this->assertEquals($expected, $result);
    }

    public function testApplyDefaultsToArray()
    {
        $expected = array(
            'component' => array(
                'class' => '\stdClass',
                'arguments' => array(),
                'calls' => array('method' => array('arg'))
            ),
            'shared' => false
        );

        $factory = new ContainerFactory();
        $result = $factory->applyDefaults(
            array(
                'component' => array(
                    'class' => '\stdClass',
                    'calls' => array('method' => 'arg')
                )
            )
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Missing required "class" key in component definition
     */
    public function testClassKeyMissing()
    {
        $factory = new ContainerFactory();
        $factory->applyDefaults(array('component' => array()));
    }

    public function testCallableDefinition()
    {
        $definition = function () {
            return new \stdClass;
        };

        $factory = new ContainerFactory();
        $result = $factory->buildDefinition($definition);

        $this->assertTrue(is_callable($result));
    }

    public function testComponentDefinition()
    {
        $definition = array(
            'class' => '\stdClass',
            'arguments' => array(),
            'calls' => array(),
        );

        $factory = new ContainerFactory();
        $result = $factory->buildDefinition($definition);

        $this->assertInstanceOf('\Moss\Container\ComponentInterface', $result);
    }

    /**
     * @expectedException \Moss\Kernel\AppException
     * @expectedExceptionMessage Invalid component format, must be callable or array with class key, got
     */
    public function testInvalidComponentFormat()
    {
        $factory = new ContainerFactory();
        $factory->buildDefinition(array('foo'));
    }
}

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
        $config = [
            'foo' => 'bar',
            'yada' => ['y', 'a', 'd', 'a'],
            'function' => [
                'component' => function () { },
            ],
            'array' => [
                'component' => [
                    'class' => '\stdClass',
                    'arguments' => [],
                    'calls' => ['method' => ['arg']]
                ],
                'shared' => false
            ]
        ];

        $factory = new ContainerFactory();
        $result = $factory->build($config);

        $this->assertInstanceOf('\Moss\Container\ContainerInterface', $result);
    }

    public function testDoNotApplyDefaultsToScalar()
    {
        $expected = [
            'foo' => 'bar'
        ];

        $factory = new ContainerFactory();
        $result = $factory->applyDefaults($expected);

        $this->assertEquals($expected, $result);
    }

    public function testDoNotApplyDefaultsToCallable()
    {
        $expected = [
            'component' => function () { },
            'shared' => false
        ];

        $factory = new ContainerFactory();
        $result = $factory->applyDefaults($expected);

        $this->assertEquals($expected, $result);
    }

    public function testApplyDefaultsToArray()
    {
        $expected = [
            'component' => [
                'class' => '\stdClass',
                'arguments' => [],
                'calls' => ['method' => ['arg']]
            ],
            'shared' => false
        ];

        $factory = new ContainerFactory();
        $result = $factory->applyDefaults(
            [
                'component' => [
                    'class' => '\stdClass',
                    'calls' => ['method' => 'arg']
                ]
            ]
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
        $factory->applyDefaults(['component' => []]);
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
        $definition = [
            'class' => '\stdClass',
            'arguments' => [],
            'calls' => [],
        ];

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
        $factory->buildDefinition(['foo']);
    }
}

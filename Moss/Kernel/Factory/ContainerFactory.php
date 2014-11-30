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


use Moss\Container\Component;
use Moss\Container\Container;
use Moss\Kernel\AppException;

class ContainerFactory
{
    protected $callableDefaults = array(
        'component' => null,
        'shared' => false
    );

    protected $classDefaults = array(
        'component' => array(
            'arguments' => array(),
            'calls' => array()
        ),
        'shared' => false
    );

    /**
     * Builds container and component definitions
     *
     * @param array $config
     *
     * @return Container
     */
    public function build(array $config)
    {
        $container = new Container();
        foreach ((array) $config as $name => $component) {
            if (!is_array($component) || !array_key_exists('component', $component)) {
                $container->register($name, $component);
                continue;
            }

            $component = $this->applyDefaults($component);
            $container->register(
                $name,
                $this->buildDefinition($component['component']),
                $component['shared']
            );
        }

        return $container;
    }

    /**
     * Applies default values or missing properties to component definition
     *
     * @param array|callable $definition
     *
     * @return array
     * @throws AppException
     */
    public function applyDefaults($definition)
    {
        if(!is_array($definition) || !isset($definition['component'])) {
            return $definition;
        }

        if(is_callable($definition['component'])) {
            return array_merge($this->callableDefaults, $definition);
        }

        if (!isset($definition['component']['class'])) {
            throw new AppException('Missing required "class" key in component definition');
        }

        $definition = array_merge_recursive($this->classDefaults, $definition);

        array_walk($definition['component']['calls'], function (&$call) { $call = (array) $call; });

        return $definition;
    }

    /**
     * Creates component definition
     *
     * @param array|callable $definition
     *
     * @return Component
     * @throws AppException
     */
    public function buildDefinition($definition)
    {
        if (is_callable($definition)) {
            return $definition;
        }

        if (array_key_exists('class', $definition)) {
            return new Component(
                $definition['class'],
                $definition['arguments'] ?: array(),
                $definition['calls'] ?: array()
            );
        }

        throw new AppException(sprintf('Invalid component format, must be callable or array with class key, got "%s"', gettype($definition)));
    }
}

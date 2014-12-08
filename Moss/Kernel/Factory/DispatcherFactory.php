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


use Moss\Dispatcher\Dispatcher;
use Moss\Dispatcher\Listener;
use Moss\Kernel\AppException;

class DispatcherFactory
{
    protected $defaults = [
        'method' => null,
        'arguments' => []
    ];


    /**
     * Builds event dispatcher and event listeners
     *
     * @param array $config
     *
     * @return Dispatcher
     */
    public function build(array $config)
    {
        $dispatcher = new Dispatcher();
        foreach ((array) $config as $name => $listener) {
            if (is_callable($listener)) {
                $dispatcher->register($name, $listener);
                continue;
            }

            $listener = $this->applyDefaults($listener);

            $dispatcher->register(
                $name,
                $this->buildDefinition($listener)
            );
        }

        return $dispatcher;
    }

    /**
     * Applies default values or missing properties to listener definition
     *
     * @param array|callable $definition
     *
     * @return array
     * @throws AppException
     */
    public function applyDefaults($definition)
    {
        if (!is_array($definition)) {
            return $definition;
        }

        if (!isset($definition['component'])) {
            throw new AppException('Missing required "component" key in listener definition');
        }

        return array_merge($this->defaults, $definition);
    }

    /**
     * Creates listener definition
     *
     * @param array|callable $definition
     *
     * @return Listener
     * @throws AppException
     */
    public function buildDefinition($definition)
    {
        if (is_callable($definition)) {
            return $definition;
        }

        if (is_array($definition) && isset($definition['component'])) {
            return new Listener(
                $definition['component'],
                $definition['method'] ?: null,
                $definition['arguments'] ?: []
            );
        }

        throw new AppException('Invalid listener format, must be callable or array with component key');
    }
}

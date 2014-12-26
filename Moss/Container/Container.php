<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Container;

/**
 * Dependency Injection Container
 *
 * @package Moss DI Container
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Container implements ContainerInterface
{

    /**
     * @var array|callable[]
     */
    protected $components = [];

    /**
     * @var array
     */
    protected $instances = [];

    /**
     * Registers component definition in container
     *
     * @param string $id
     * @param mixed  $definition
     * @param bool   $shared
     *
     * @return $this
     * @throws ContainerException
     */
    public function register($id, $definition, $shared = false)
    {
        if (is_object($definition) && !is_callable($definition)) {
            $this->instances[$id] = $definition;

            return $this;
        }

        $this->components[$id] = $definition;

        if ($shared) {
            $this->instances[$id] = null;
        }

        return $this;
    }

    /**
     * Unregisters component from container
     *
     * @param string $id
     *
     * @return $this
     */
    public function unregister($id)
    {
        if (array_key_exists($id, $this->components)) {
            unset($this->components[$id]);
        }

        if (array_key_exists($id, $this->instances)) {
            unset($this->instances[$id]);
        }

        return $this;
    }

    /**
     * Returns true if component exists in container
     *
     * @param string $id
     *
     * @return bool
     */
    public function exists($id)
    {
        if (array_key_exists($id, $this->components)) {
            return true;
        }

        if (isset($this->instances[$id])) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if component is shared
     *
     * @param string $id
     *
     * @return bool
     */
    public function isShared($id)
    {
        return array_key_exists($id, $this->instances);
    }

    /**
     * Returns component instance or value
     *
     * @param string $id
     *
     * @return mixed
     * @throws ContainerException
     */
    public function &get($id)
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        $keys = explode('.', $id);

        $node = & $this->components;
        while ($key = array_shift($keys)) {
            if (!is_array($node) || !array_key_exists($key, $node)) {
                throw new ContainerException(sprintf('Invalid or unknown component/parameter identifier "%s"', $id));
            }

            $node = & $node[$key];
        }

        if (is_callable($node)) {
            $result = $node($this);
        } else {
            $result = $node;
        }

        if (array_key_exists($id, $this->instances)) {
            $this->instances[$id] = & $result;
        }

        return $result;
    }
}

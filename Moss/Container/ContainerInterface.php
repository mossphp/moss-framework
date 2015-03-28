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
 * Dependency Injection Container interface
 *
 * @package Moss DI Container
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface ContainerInterface
{
    /**
     * Registers component definition in container
     *
     * @param string $id
     * @param mixed  $definition
     * @param bool   $shared
     *
     * @return $this
     */
    public function register($id, $definition, $shared = false);

    /**
     * Unregisters component from container
     *
     * @param string $id
     *
     * @return $this
     */
    public function unregister($id);

    /**
     * Returns array registered components and parameters
     *
     * @return array
     */
    public function retrieve();

    /**
     * Returns true if component exists in container
     *
     * @param string $id
     *
     * @return bool
     */
    public function exists($id);

    /**
     * Returns true if component is shared
     *
     * @param string $id
     *
     * @return bool
     */
    public function isShared($id);

    /**
     * Returns component instance or value
     *
     * @param string $id
     *
     * @return mixed
     * @throws ContainerException
     */
    public function &get($id);
}

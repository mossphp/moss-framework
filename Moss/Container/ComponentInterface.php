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
 * Dependency Injection Component interface
 *
 * @package Moss DI Container
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface ComponentInterface
{

    /**
     * Returns component instance
     *
     * @param ContainerInterface $container
     *
     * @return object
     */
    public function __invoke(ContainerInterface $container = null);

    /**
     * Returns component instance
     *
     * @param ContainerInterface $container
     *
     * @return object
     */
    public function get(ContainerInterface $container = null);
}

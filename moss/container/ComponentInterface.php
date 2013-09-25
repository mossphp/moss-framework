<?php
namespace moss\container;

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
     * @param ContainerInterface $Container
     *
     * @return object
     */
    public function get(ContainerInterface $Container = null);
}
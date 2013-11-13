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

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

use Moss\Http\Router\Route;
use Moss\Http\Router\RouteInterface;
use Moss\Http\Router\Router;
use Moss\Kernel\AppException;

/**
 * Class RouterFactory
 *
 * @package Moss\Kernel
 */
class RouterFactory
{
    protected $defaults = [
        'arguments' => [],
        'methods' => []
    ];


    /**
     * Builds router and routes
     *
     * @param array $config
     *
     * @return Router
     */
    public function build(array $config)
    {
        $router = new Router();
        foreach ($config as $name => $route) {
            if ($route instanceof RouteInterface) {
                $router->register($name, $route);
                continue;
            }

            $route = $this->applyDefaults($route);

            $router->register(
                $name,
                $this->createDefinition($route)
            );
        }

        return $router;
    }

    /**
     * Applies default values or missing properties to route definition
     *
     * @param array $definition
     *
     * @return array
     * @throws AppException
     */
    public function applyDefaults($definition)
    {
        if (!isset($definition['pattern'])) {
            throw new AppException('Missing required "pattern" key in route definition');
        }

        if (!isset($definition['controller'])) {
            throw new AppException('Missing required "controller" key in route definition');
        }

        return array_merge($this->defaults, $definition);
    }

    /**
     * Creates route definition
     *
     * @param array|callable $definition
     *
     * @return Route
     * @throws AppException
     */
    public function createDefinition($definition)
    {
        return new Route(
            $definition['pattern'],
            $definition['controller'],
            $definition['arguments'] ? (array) $definition['arguments'] : [],
            $definition['methods'] ? (array) $definition['methods'] : []
        );
    }
}

<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Http\Router;

use Moss\Http\Request\RequestInterface;

/**
 * Router interface
 *
 * @package Moss Router
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface RouterInterface
{

    /**
     * Registers route definition into routing table
     *
     * @param string         $name
     * @param RouteInterface $routeDefinition
     *
     * @return Router|RouterInterface
     */
    public function register($name, RouteInterface $routeDefinition);

    /**
     * Returns array containing all registered routes
     *
     * @return array|RouteInterface[]
     */
    public function retrieve();

    /**
     * Matches request to route
     * Throws RangeException if no matching route found
     *
     * @param RequestInterface $request
     *
     * @return Router|RouterInterface
     */
    public function match(RequestInterface $request);

    /**
     * Makes link
     * If corresponding route exists - friendly link is generated, otherwise normal
     *
     * @param null|string $name      controller identifier, if null request controller is used
     * @param array       $arguments additional arguments
     *
     * @return string
     */
    public function make($name = null, $arguments = array());
}

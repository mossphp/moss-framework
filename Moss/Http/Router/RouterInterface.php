<?php
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
     * @throws RouteException
     */
    public function match(RequestInterface $request);

    /**
     * Makes link
     * If corresponding route exists - friendly link is generated, otherwise normal
     *
     * @param null|string $controller    controller identifier, if null request controller is used
     * @param array       $arguments     additional arguments
     * @param bool        $forceNormal   if true forces normal link
     * @param bool        $forceRelative if true forces direct link
     *
     * @return string
     * @throws \InvalidArgumentException|\RangeException
     */
    public function make($controller = null, $arguments = array(), $forceNormal = false, $forceRelative = false);
}

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
 * Router
 * Responsible for matching Request to route and URL creation
 *
 * @package Moss Router
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Router implements RouterInterface
{

    protected $defaults = array(
        'host' => null,
        'controller' => null,
        'locale' => null,
        'format' => null
    );

    /**
     * @var array|RouteInterface[]
     */
    protected $routes = array();

    /**
     * Creates router instance
     *
     * @param array $defaults
     */
    public function __construct($defaults = array())
    {
        $this->defaults = array_merge($this->defaults, (array) $defaults);
    }

    /**
     * Registers route definition into routing table
     *
     * @param string $name
     * @param RouteInterface $routeDefinition
     *
     * @return $this
     */
    public function register($name, RouteInterface $routeDefinition)
    {
        $this->routes[$name] = $routeDefinition;

        return $this;
    }

    /**
     * Returns array containing all registered routes
     *
     * @return array|RouteInterface[]
     */
    public function retrieve()
    {
        return (array) $this->routes;
    }

    /**
     * Matches request to route
     * Throws RangeException if no matching route found
     *
     * @param RequestInterface $request
     *
     * @return string
     * @throws RouterException
     */
    public function match(RequestInterface $request)
    {
        foreach ($this->routes as $route) {
            if (!$route->match($request)) {
                continue;
            }

            foreach ($route->arguments() as $key => $value) {
                $request->query()
                    ->set($key, $value);
            }

            $request->locale(
                $request->query()
                    ->get('locale')
            );
            $request->format(
                $request->query()
                    ->get('format')
            );

            $request->controller($route->controller());

            $this->defaults = array(
                'host' => $request->baseName(),
                'controller' => $request->controller(),
                'locale' => $request->locale(),
                'format' => $request->format()
            );

            return $request->controller();
        }

        throw new RouterException('Route for "' . $request->path() . '" not found!');
    }

    /**
     * Makes link
     * If corresponding route exists - friendly link is generated, otherwise normal
     *
     * @param null|string $controller controller identifier, if null request controller is used
     * @param array       $arguments  additional arguments
     *
     * @return string
     * @throws RouterException
     */
    public function make($controller = null, $arguments = array())
    {
        $controller = $this->resolveController($controller);

        if (isset($this->routes[$controller])) {
            return $this->routes[$controller]->make($this->defaults['host'], $arguments);
        }

        foreach ($this->routes as $route) {
            if (!$route->check($controller, $arguments)) {
                continue;
            }

            return $route->make($this->defaults['host'], $arguments);
        }

        throw new RouterException('Unable to make url, matching route for "' . $controller . '" not found');
    }

    private function resolveController($controller)
    {
        if ($controller) {
            return $controller;
        }

        if (!isset($this->defaults['controller'])) {
            throw new RouterException('Unable to make "self" url - default controller was not defined.');
        }

        return $this->defaults['controller'];
    }
}
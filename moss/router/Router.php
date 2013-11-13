<?php
namespace moss\router;

use moss\http\request\RequestInterface;

/**
 * Router
 * Responsible for matching Request to route and URL creation
 *
 * @package Moss Router
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Router implements RouterInterface
{

    protected $defaults = array('host' => null);

    protected $routeNormal = true;
    protected $fallbackNormal = true;
    protected $forceRelative = false;

    /** @var array|RouteInterface[] */
    protected $routes = array();

    /**
     * Creates router instance
     *
     * @param bool $allowNormal
     * @param bool $allowFallback
     * @param bool $forceRelative
     */
    public function __construct($allowNormal = true, $allowFallback = true, $forceRelative = false)
    {
        $this->allowNormal($allowNormal);
        $this->fallbackNormal($allowFallback);
        $this->forceRelative($forceRelative);
    }

    /**
     * If set to true, routes normal urls
     * Otherwise ignores them
     *
     * @param null|bool $route
     *
     * @return bool
     */
    public function allowNormal($route = null)
    {
        if ($route !== null) {
            $this->routeNormal = (bool) $route;
        }

        return $this->routeNormal;
    }

    /**
     * If set to true, generates normal urls when route definition is missing
     *
     * @param null|bool $fallback
     *
     * @return bool
     */
    public function fallbackNormal($fallback = null)
    {
        if ($fallback !== null) {
            $this->fallbackNormal = (bool) $fallback;
        }

        return $this->fallbackNormal;
    }

    /**
     * If true, generates only absolute urls
     *
     * @param null|bool $force
     *
     * @return bool
     */
    public function forceRelative($force = null)
    {
        if ($force !== null) {
            $this->forceRelative = (bool) $force;
        }

        return $this->forceRelative;
    }

    /**
     * Registers route definition into routing table
     *
     * @param string         $name
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
        if ($this->routeNormal && $request->getQuery('controller')) {
            $request->controller(str_replace('_', ':', $request->getQuery('controller')));

            $this->defaults['host'] = $request->baseName();
            $this->defaults['controller'] = $request->controller();
            $this->defaults['locale'] = $request->locale();
            $this->defaults['format'] = $request->format();

            return $request->controller();
        }

        foreach ($this->routes as $route) {
            if (!$route->match($request)) {
                continue;
            }

            foreach ($route->arguments() as $key => $value) {
                $request->getQuery($key, $value);
            }

            if ($request->getQuery('locale')) {
                $request->locale($request->getQuery('locale'));
            }

            if ($request->getQuery('format')) {
                $request->format($request->getQuery('format'));
            }

            $request->controller($route->controller());

            $this->defaults['host'] = $request->baseName();
            $this->defaults['controller'] = $request->controller();
            $this->defaults['locale'] = $request->locale();
            $this->defaults['format'] = $request->format();

            return $request->controller();
        }

        throw new RouterException('Route for "' . $request->url() . '" not found!');
    }

    /**
     * Makes link
     * If corresponding route exists - friendly link is generated, otherwise normal
     *
     * @param null|string $controller    controller identifier, if null request controller is used
     * @param array       $arguments     additional arguments
     * @param bool        $forceNormal   if true forces normal link
     * @param bool        $forceRelative if true forces absolute link
     *
     * @return string
     * @throws RouterException
     */
    public function make($controller = null, $arguments = array(), $forceNormal = false, $forceRelative = false)
    {
        $forceRelative = $forceRelative || $this->forceRelative;

        if (!$controller) {
            if (!isset($this->defaults['controller'])) {
                throw new RouterException('Unable to make \'self\' url - default controller is not defined.');
            }

            $controller = $this->defaults['controller'];
        }

        if ($forceNormal) {
            return $this->makeNormal($this->defaults['host'], $controller, $arguments, $forceRelative);
        }

        if (isset($this->routes[$controller])) {
            return $this->routes[$controller]->make($this->defaults['host'], $arguments, $forceRelative);
        }

        foreach ($this->routes as $route) {
            if (!$route->check($controller, $arguments)) {
                continue;
            }

            return $route->make($this->defaults['host'], $arguments, $forceRelative);
        }

        if ($this->fallbackNormal) {
            return $this->makeNormal($this->defaults['host'], $controller, $arguments, $forceRelative);
        }

        throw new RouterException('Unable to make url. Matching route for "' . $controller . '" not found');
    }

    /**
     * Makes normal url
     *
     * @param string $host
     * @param string $controller
     * @param array  $arguments
     * @param bool   $forceRelative
     *
     * @return string
     */
    protected function makeNormal($host, $controller, $arguments, $forceRelative)
    {
        $arguments = (empty($arguments) ? null : '&' . http_build_query(array_filter($arguments), null, '&'));
        $url = '?controller=' . preg_replace('/[^a-z0-9]+/i', '_', $controller) . $arguments;

        return (empty($host) || $forceRelative == true ? './' : rtrim($host, '/') . '/') . ltrim($url, './');
    }
}

<?php
namespace moss\router;

use moss\router\RouterInterface;
use moss\router\RouteInterface;
use moss\router\RouterException;
use moss\http\request\RequestInterface;

/**
 * Router
 * Responsible for matching Request to route and URL creation
 *
 * @package Moss Router
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Router implements RouterInterface {

	protected $defaults = array('host' => null);

	protected $allowFallback = true;
	protected $forceAbsolute = false;

	/** @var array|RouteInterface[] */
	protected $routes = array();

	/**
	 * Creates router instance
	 *
	 * @param bool $allowFallback
	 * @param bool $forceAbsolute
	 */
	public function __construct($allowFallback = true, $forceAbsolute = false) {
		$this->allowFallback = (bool) $allowFallback;
		$this->forceAbsolute = (bool) $forceAbsolute;
	}

	/**
	 * Registers route definition into routing table
	 *
	 * @param string         $name
	 * @param RouteInterface $RouteDefinition
	 *
	 * @return $this
	 */
	public function register($name, RouteInterface $RouteDefinition) {
		$this->routes[$name] = $RouteDefinition;

		return $this;
	}

	/**
	 * Returns array containing all registered routes
	 *
	 * @return array|RouteInterface[]
	 */
	public function retrieve() {
		return (array) $this->routes;
	}

	/**
	 * Matches request to route
	 * Throws RangeException if no matching route found
	 *
	 * @param RequestInterface $Request
	 *
	 * @return string
	 * @throws RouterException
	 */
	public function match(RequestInterface $Request) {
		if($Request->query('controller')) {
			$Request->controller($Request->query('controller'));

			$this->defaults['host'] = $Request->host();
			$this->defaults['controller'] = $Request->controller();
			$this->defaults['locale'] = $Request->locale();
			$this->defaults['format'] = $Request->format();

			return $Request->controller();
		}

		foreach($this->routes as $Route) {
			if(!$Route->match($Request)) {
				continue;
			}

			foreach($Route->arguments() as $key => $value) {
				$Request->query($key, $value);
			}

			if($Request->query('locale')) {
				$Request->locale($Request->query('locale'));
			}

			if($Request->query('format')) {
				$Request->format($Request->query('format'));
			}

			$Request->controller($Route->controller());

			$this->defaults['host'] = $Request->host();
			$this->defaults['controller'] = $Request->controller();
			$this->defaults['locale'] = $Request->locale();
			$this->defaults['format'] = $Request->format();

			return $Request->controller();
		}

		throw new RouterException('Route for "' . $Request->url() . '" not found!');
	}

	/**
	 * Makes link
	 * If corresponding route exists - friendly link is generated, otherwise normal
	 *
	 * @param null|string $controller        controller identifier, if null request controller is used
	 * @param array       $arguments         additional arguments
	 * @param bool        $forceNormal       if true forces normal link
	 * @param bool        $forceAbsolute     if true forces absolute link
	 *
	 * @return string
	 * @throws RouterException
	 */
	public function make($controller = null, $arguments = array(), $forceNormal = false, $forceAbsolute = false) {
		$forceAbsolute = $forceAbsolute || $this->forceAbsolute;

		if(!$controller) {
			if(!isset($this->defaults['controller'])) {
				throw new RouterException('Unable to make \'self\' url - default controller is not defined.');
			}

			$controller = $this->defaults['controller'];
		}

		if($forceNormal) {
			return $this->makeNormal($this->defaults['host'], $controller, $arguments, $forceAbsolute);
		}

		if(isset($this->routes[$controller])) {
			return $this->routes[$controller]->make($this->defaults['host'], $arguments, $forceAbsolute);
		}

		foreach($this->routes as $Route) {
			if(!$Route->check($controller, $arguments)) {
				continue;
			}

			return $Route->make($this->defaults['host'], $arguments, $forceAbsolute);
		}

		if($this->allowFallback) {
			return $this->makeNormal($this->defaults['host'], $controller, $arguments, $forceAbsolute);
		}

		throw new RouterException('Unable to make url. Matching route for "' . $controller . '" not found');
	}

	/**
	 * Makes normal url
	 *
	 * @param string $host
	 * @param string $controller
	 * @param array  $arguments
	 * @param bool   $forceAbsolute
	 *
	 * @return string
	 */
	protected function makeNormal($host, $controller, $arguments, $forceAbsolute) {
		$url = '?controller=' . preg_replace('/[^a-z0-9]+/', '_', $controller) . (empty($arguments) ? null : '&' . http_build_query($arguments, null, '&'));

		if(empty($host) || $forceAbsolute == false) {
			$host = '';
		}

		return (empty($host) || $forceAbsolute == false ? null : rtrim($host, '/') . '/') . ltrim($url, './');
	}
}
<?php
namespace moss\router;

use moss\router\RouteInterface;
use moss\http\request\RequestInterface;

/**
 * Route representation
 *
 * @package Moss Router
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Route implements RouteInterface {

	protected $controller;
	protected $pattern;
	protected $requirements = array();
	protected $defaults = array();
	protected $arguments = array();
	protected $host;
	protected $schema;
	protected $methods;

	/**
	 * Constructor
	 *
	 * @param string          $pattern
	 * @param string|\Closure $controller
	 * @param array           $arguments
	 */
	public function __construct($pattern, $controller, $arguments = array()) {
		$this->pattern = $pattern;
		$this->controller = $controller;
		$this->pattern = preg_replace_callback('/(\(?\{([^}]+)\}\)?)/i', array($this, 'callback'), $this->pattern, \PREG_SET_ORDER);

		if(!empty($arguments)) {
			$this->arguments($arguments);
		}
	}

	/**
	 * Builds pattern regular expression
	 *
	 * @param array  $match
	 * @param string $default
	 *
	 * @return string
	 */
	private function callback($match, $default = '[a-z0-9-._]') {
		if(strpos($match[2], ':') === false) {
			$match[2] .= ':'.$default;
		}

		list($key, $regexp) = explode(':', $match[2]);
		if($match[0][0] == '(') {
			$this->requirements[$key] = $regexp . '*';

			return '#' . $key . '#';
		}

		$this->requirements[$key] = $regexp . '+';
		$this->arguments[$key] = null;
		$this->defaults[$key] = null;

		return '#' . $key . '#';
	}

	/**
	 * Rebuilds pattern from regular expression
	 *
	 * @return string
	 */
	public function pattern() {
		preg_match_all('/#([^#]+)#/i', $this->pattern, $matches, \PREG_SET_ORDER);
		$r = array();

		foreach($matches as $match) {
			$r['#' . $match[1] . '#'] = array_key_exists($match[1], $this->defaults) ? '{' . $match[1] . '}' : '({' . $match[1] . '})';
		}

		return strtr($this->pattern, $r);
	}

	/**
	 * Returns controller
	 *
	 * @return string
	 */
	public function controller() {
		return $this->controller;
	}

	/**
	 * Sets value requirements for each argument in pattern
	 *
	 * @param array $requirements
	 *
	 * @return array
	 * @throws RouteException
	 */
	public function requirements($requirements = array()) {
		if(empty($requirements)) {
			return $this->requirements;
		}

		foreach(array_keys($this->requirements) as $key) {
			if(!array_key_exists($key, $requirements)) {
				throw new RouteException(sprintf('Missing requirement pattern value for "%s"', $key));
			}
		}


		$this->requirements = $requirements;

		return $this->requirements;
	}

	/**
	 * Sets values for each argument in pattern
	 *
	 * @param array $arguments
	 *
	 * @return array
	 * @throws RouteException
	 */
	public function arguments($arguments = array()) {
		if(empty($arguments)) {
			return $this->arguments;
		}

		foreach(array_keys($this->defaults) as $key) {
			if(!array_key_exists($key, $arguments)) {
				throw new RouteException(sprintf('Missing required default value for "%s"', $key));
			}

			$this->defaults[$key] = $arguments[$key];
		}

		$this->arguments = $arguments;

		return $this->arguments;
	}

	/**
	 * Sets host requirement
	 *
	 * @param null|string $host
	 *
	 * @return $this
	 */
	public function host($host = null) {
		$this->host = empty($host) ? null : str_replace('{basename}', '#basename#', $host);

		return $this;
	}

	/**
	 * Sets allowed schema
	 *
	 * @param string $schema
	 *
	 * @return $this
	 */
	public function schema($schema = null) {
		$this->schema = empty($schema) ? null : $schema;

		return $this;
	}

	/**
	 * Sets allowed methods
	 *
	 * @param array $methods
	 *
	 * @return $this
	 */
	public function methods($methods = array()) {
		$this->methods = empty($methods) ? array() : (array) $methods;

		foreach($this->methods as &$method) {
			$method = strtoupper($method);
			unset($method);
		}

		return $this;
	}

	/**
	 * Returns true if matches request, otherwise returns false
	 *
	 * @param RequestInterface $Request
	 *
	 * @return bool
	 */
	public function match(RequestInterface $Request) {
		if(!empty($this->schema) && strpos($Request->schema(), $this->schema) === false) {
			return false;
		}

		if(!empty($this->methods) && !in_array($Request->method(), $this->methods)) {
			return false;
		}

		if(!empty($this->host) && !preg_match('/^(https?|ftp):\/\/' . str_replace('#basename#', '.*', preg_quote($this->host)) . '$/', $Request->host())) {
			return false;
		}

		$vars = array();
		foreach($this->requirements as $v => $exp) {
			$k = '#' . $v . '#';
			$vars[$k] = '(?P<' . $v . '>' . $exp . ')';
			if(substr($exp, -1) == '*') {
				$vars[$k] = '?' . $vars[$k] . '?';
			}
		}

		$regexp = strtr(preg_quote($this->pattern, '/'), $vars);
		$regexp .= substr($regexp, -1) == '/' ? '?' : null;
		$regexp = '/^' . $regexp . '$/i';

		if(!preg_match_all($regexp, $Request->url(), $matches, \PREG_SET_ORDER)) {
			return false;
		}

		$arguments = array();
		foreach($matches[0] as $k => $v) {
			if(is_numeric($k)) {
				continue;
			}

			$arguments[$k] = $v;
		}

		$this->arguments($arguments);

		return true;
	}

	/**
	 * Check if arguments fit to
	 *
	 * @param string $controller
	 * @param array  $arguments
	 *
	 * @return mixed
	 */
	public function check($controller, $arguments = array()) {
		if($this->controller() !== $controller) {
			return false;
		}

		foreach(array_keys($this->defaults) as $k) {
			if(!isset($arguments[$k])) {
				return false;
			}

			if(!preg_match('/^' . $this->requirements[$k] . '$/i', $arguments[$k])) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Creates route url
	 *
	 * @param null|string $host
	 * @param array       $arguments
	 * @param bool        $forceRelative
	 *
	 * @return string
	 * @throws RouteException
	 */
	public function make($host = null, $arguments = array(), $forceRelative = false) {
		foreach($this->requirements as $k => $r) {
			if(!isset($arguments[$k]) && !array_key_exists($k, $this->defaults)) {
				continue;
			}

			if(!isset($arguments[$k])) {
				throw new RouteException(sprintf('Missing value for argument "%s" in route "%s"', $k, $this->pattern()));
			}

			if(!preg_match('/^' . $r . '$/i', $arguments[$k])) {
				throw new RouteException(sprintf('Invalid argument value "%s" for argument "%s" in route "%s"', $k, $arguments[$k], $this->pattern()));
			}
		}

		foreach($this->requirements as $k => $v) {
			if(isset($arguments[$k])) {
				$arguments[$k] = $this->strip($arguments[$k]);
				continue;
			}

			$arguments[$k] = isset($this->defaults[$k]) ? $this->defaults[$k] : null;
		}

		$uArr = array();
		$qArr = array();

		foreach($arguments as $k => $v) {
			if(isset($this->requirements[$k])) {
				$uArr['#' . $k . '#'] = $v;
				continue;
			}

			$qArr[$k] = $v;
		}

		$url = strtr($this->pattern, $uArr);
		$url = str_replace('//', '/', $url);

		if(!empty($qArr)) {
			$url .= '?' . http_build_query($qArr, null, '&');
		}

		$url = ltrim($url, './');

		if(!empty($this->host) && empty($host)) {
			throw new RouteException('Unable to create absolute url. Invalid or empty host name');
		}

		if(empty($this->host) && (empty($host) || $forceRelative == true)) {
			return './' . $url;
		}

		$schema = null;
		if(strpos($host, '://') !== false) {
			list($schema, $host) = explode('://', rtrim($host, '/'));
		}
		if($this->host && !preg_match('/^' . str_replace('#basename#', '.*', preg_quote($this->host)) . '$/', $host)) {
			$host = str_replace('#basename#', $host, $this->host);
		}

		return ($schema ? $schema . '://' : null) . $host . '/' . $url;
	}

	/**
	 * Strips string from non ASCII chars
	 *
	 * @param string $urlString string to strip
	 * @param string $separator char replacing non ASCII chars
	 *
	 * @return string
	 */
	protected function strip($urlString, $separator = '-') {
		$urlString = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $urlString);
		$urlString = strtolower($urlString);
		$urlString = preg_replace('#[^\w \-\.]+#i', null, $urlString);
		$urlString = preg_replace('/[ -\.]+/', $separator, $urlString);
		$urlString = trim($urlString, '-.');

		return $urlString;
	}
}
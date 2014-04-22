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
 * Route representation
 *
 * @package Moss Router
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Route implements RouteInterface
{
    protected $controller;

    protected $regex;
    protected $pattern;


    protected $requirements = array();
    protected $arguments = array();
    protected $conditionals = array();

    protected $host;
    protected $schema;
    protected $methods;

    /**
     * Constructor
     *
     * @param string          $pattern
     * @param string|\Closure $controller
     * @param array           $arguments
     * @param array           $methods
     */
    public function __construct($pattern, $controller, $arguments = array(), $methods = array())
    {
        $this->controller = $controller;
        $this->pattern = $pattern;
        $this->regex = preg_replace_callback('/(\()?(\{([^}]+)\})(?(1)([^()]*)|())(\))?/i', array($this, 'callback'), $pattern, \PREG_SET_ORDER);

        foreach ($arguments as $key => $value) {
            if (!isset($this->requirements[$key])) {
                $this->requirements[$key] = $value;
            }

            if (!isset($this->conditionals[$key])) {
                $this->conditionals[$key] = false;
            }

            $this->arguments[$key] = $value;
        }

        $this->methods($methods);
    }

    /**
     * Builds pattern regular expression
     *
     * @param array  $match
     * @param string $default
     *
     * @return string
     * @throws RouteException
     */
    private function callback($match, $default = '[a-z0-9-._]')
    {
        if (strpos($match[3], ':') === false) {
            $match[3] .= ':' . $default;
        }

        list($key, $regexp) = explode(':', $match[3]);

        if (in_array(substr($regexp, -1), array('+', '*', '?'))) {
            throw new RouteException('Route must not end with quantification token');
        }

        if ($match[0][0] == '(') {
            $this->requirements[$key] = $regexp . '*' . '(' . preg_quote($match[4], '/') . ')?';
            $this->conditionals[$key] = $match[4];

            return $this->key($key);
        }

        $this->requirements[$key] = $regexp . '+';
        $this->conditionals[$key] = null;
        $this->arguments[$key] = null;

        return $this->key($key) . $match[4];
    }

    /**
     * Rebuilds pattern from regular expression
     *
     * @return string
     */
    public function pattern()
    {
        if (!empty($this->pattern)) {
            return $this->pattern;
        }

        $arguments = array();
        foreach ($this->requirements as $key => $v) {
            if ($this->conditionals[$key]) {
                $v = substr($v, 0, -strlen(preg_quote($this->conditionals[$key])) + 4);
            }

            $pattern = '{%s:%s}%s';
            if (substr($v, -1) === '*') {
                $pattern = '({%s:%s}%s)';
            }

            $arguments[$this->key($key)] = sprintf($pattern, $key, substr($v, 0, -1), $this->conditionals[$key]);
        }

        return $this->pattern = strtr($this->regex, $arguments);
    }

    /**
     * Returns controller
     *
     * @return string
     */
    public function controller()
    {
        return $this->controller;
    }

    /**
     * Sets regex for each of required values
     *
     * @param array $requirements
     *
     * @return array
     * @throws RouteException
     */
    public function requirements($requirements = array())
    {
        if (empty($requirements)) {
            return $this->requirements;
        }

        foreach (array_keys($this->requirements) as $key) {
            if (!array_key_exists($key, $requirements)) {
                continue;
            }

            $this->requirements[$key] = $requirements[$key];
        }

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
    public function arguments($arguments = array())
    {
        if (empty($arguments)) {
            return $this->arguments;
        }

        foreach ($arguments as $key => $value) {
            if (!isset($this->requirements[$key])) {
                $this->arguments[$key] = $value;
                continue;
            }

            $this->assertArgumentValue($this->requirements[$key], $value, $key);
            $this->arguments[$key] = $value;
        }

        return $this->arguments;
    }

    /**
     * Sets host requirement
     *
     * @param null|string $host
     *
     * @return $this
     */
    public function host($host = null)
    {
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
    public function schema($schema = null)
    {
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
    public function methods($methods = array())
    {
        $methods = (array) $methods;
        foreach ($methods as &$method) {
            $this->methods[] = strtoupper($method);
        }

        return $this;
    }

    /**
     * Returns true if matches request, otherwise returns false
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function match(RequestInterface $request)
    {
        if (!empty($this->schema) && strpos($request->schema(), $this->schema) === false) {
            return false;
        }

        if (!empty($this->methods) && !in_array($request->method(), $this->methods)) {
            return false;
        }

        if (!empty($this->host) && !preg_match('/^' . str_replace('#basename#', '.*', preg_quote($this->host)) . '$/', $request->host())) {
            return false;
        }

        $vars = array();
        foreach ($this->requirements as $v => $exp) {
            $k = $this->key($v);
            $vars[$k] = '(?P<' . $v . '>' . $exp . ')';
            if ($this->conditionals[$v]) {
                $vars[$k] = '?' . $vars[$k] . '?';
            }
        }

        $regexp = strtr(preg_quote($this->regex, '/'), $vars);
        $regexp .= substr($regexp, -1) == '/' ? '?' : null;
        $regexp = '/^' . $regexp . '$/i';

        if (!preg_match_all($regexp, $request->path(), $matches, \PREG_SET_ORDER)) {
            return false;
        }

        foreach ($matches[0] as $k => $v) {
            if (is_numeric($k)) {
                continue;
            }

            $this->arguments[$k] = $this->conditionals[$k] ? rtrim($v, $this->conditionals[$k]) : $v;
        }

        return true;
    }

    /**
     * Check if route should be used to make url
     *
     * @param string $controller
     * @param array  $arguments
     *
     * @return mixed
     */
    public function check($controller, $arguments = array())
    {
        if ($this->controller() !== $controller) {
            return false;
        }

        foreach ($this->requirements as $key => $regex) {
            $value = isset($arguments[$key]) ? $arguments[$key] : null;

            if (!preg_match('/^' . $regex . '$/i', $value)) {
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
    public function make($host = null, $arguments = array(), $forceRelative = false)
    {
        foreach (array_keys($this->conditionals) as $key) {
            if (isset($arguments[$key])) {
                continue;
            }

            $arguments[$key] = null;
        }

        foreach ($this->requirements as $key => $regex) {
            if (!array_key_exists($key, $arguments)) {
                throw new RouteException(sprintf('Missing value for argument "%s" in route "%s"', $key, $this->pattern()));
            }

            $this->assertArgumentValue($regex, $arguments[$key], $key);
        }

        $url = array();
        $query = array();

        foreach ($arguments as $key => $val) {
            if (isset($this->requirements[$key])) {
                if ($this->conditionals[$key] && empty($val)) {
                    $url[$this->key($key)] = null;
                } else {
                    $url[$this->key($key)] = $this->strip($val) . $this->conditionals[$key];
                }

                continue;
            }

            if (isset($this->arguments[$key])) {
                continue;
            }

            $query[$key] = $val;
        }

        $url = strtr($this->regex, $url);
        $url = str_replace('//', '/', $url);

        if (!empty($query)) {
            $url .= '?' . http_build_query($query, null, '&');
        }

        $url = ltrim($url, './');

        if (!empty($this->host) && empty($host)) {
            throw new RouteException('Unable to create absolute url. Invalid or empty host name');
        }

        if (empty($this->host) && (empty($host) || $forceRelative == true)) {
            return './' . $url;
        }

        $schema = null;
        if (strpos($host, '://') !== false) {
            list($schema, $host) = explode('://', rtrim($host, '/'));
        }

        if ($this->host && !preg_match('/^' . str_replace('#basename#', '.*', preg_quote($this->host)) . '$/', $host)) {
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
    private function strip($urlString, $separator = '-')
    {
        $urlString = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $urlString);
        $urlString = strtolower($urlString);
        $urlString = preg_replace('#[^\w \-\.]+#i', null, $urlString);
        $urlString = preg_replace('/[ -\.]+/', $separator, $urlString);
        $urlString = trim($urlString, '-.');

        return $urlString;
    }

    private function key($key)
    {
        return '#' . $key . '#';
    }

    private function assertArgumentValue($regex, $value, $key)
    {
        if (!preg_match('/^' . $regex . '$/i', $value)) {
            throw new RouteException(sprintf('Invalid value "%s" for argument "%s" in route "%s"', $key, $value, $this->pattern()));
        }
    }
}

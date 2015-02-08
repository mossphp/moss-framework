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
    const REGEX = '/(\()?(\{([^}]+)\})(?(1)([^()]*)|())(\))?(\/)?/i';

    protected $controller;
    protected $pattern;
    protected $regex;

    protected $requirements = [];
    protected $constraints = [];
    protected $builders = [];
    protected $arguments = [];

    protected $host;
    protected $schema;
    protected $methods;

    /**
     * Constructor
     *
     * @param string          $pattern
     * @param string|callable $controller
     * @param array           $arguments
     * @param array           $methods
     */
    public function __construct($pattern, $controller, array $arguments = [], array $methods = [])
    {
        $this->controller = $controller;
        $this->pattern = $pattern;

        preg_match_all(self::REGEX, $this->pattern, $matches, \PREG_SET_ORDER);

        $this->regex = $this->buildRegexp($this->pattern, $matches);
        $this->requirements = $this->buildRequirements($matches);
        $this->builders = $this->buildBuilders($this->pattern, $matches);

        $this->arguments = array_fill_keys(array_keys($this->requirements), null);

        foreach ($arguments as $key => $value) {
            if (!isset($this->requirements[$key])) {
                $this->requirements[$key] = $value;
            }

            $this->arguments[$key] = $value;
        }

        $this->methods($methods);
    }

    /**
     * Builds regular expression for route matching
     *
     * @param string $pattern
     * @param array  $matches
     *
     * @return string
     * @throws RouteException
     */
    protected function buildRegexp($pattern, array $matches)
    {
        $src = [];
        $trg = [];
        foreach ($matches as $match) {
            list($key, $regexp) = $this->splitSegment($match[3]);

            if (in_array(substr($regexp, -1), ['+', '*', '?'])) {
                throw new RouteException('Route must not end with quantification token');
            }

            $src[rtrim($match[0], '/')] = $key;

            $trg[$key] = '(?P<' . $key . '>' . $regexp . '+)';

            if ($match[1] == '(') {
                $trg[$key] = '(' . $trg[$key] . $this->buildConditionalSlash(preg_quote($match[4], '/')) . ')?';
            }
        }

        $pattern = strtr($pattern, $src);
        $pattern = preg_quote($pattern, '/');
        $pattern = strtr($pattern, $trg);
        $pattern = str_replace('\/((?P', '\/?((?P', $pattern);
        $pattern = $this->buildConditionalSlash($pattern);

        return '/^' . $pattern . '$/i';
    }

    /**
     * Adds conditional slash if slash is last char in string (preg quoted)
     *
     * @param string $pattern
     *
     * @return string
     */
    protected function buildConditionalSlash($pattern)
    {
        if (substr($pattern, -2) === '\/') {
            $pattern .= '?';
        }

        return $pattern;
    }

    /**
     * Builds array of regular expression for route arguments
     *
     * @param array $matches
     *
     * @return array
     */
    protected function buildRequirements(array $matches)
    {
        $result = [];
        foreach ($matches as $match) {
            list($key, $regexp) = $this->splitSegment($match[3]);
            $result[$key] = $regexp . ($match[1] == '(' ? '*' : '+');
        }

        return $result;
    }

    /**
     * Builds array allowing route creation
     *
     * @param string $pattern
     * @param array  $matches
     *
     * @return array
     */
    protected function buildBuilders($pattern, array $matches)
    {
        $result = [
            'pattern' => $pattern,
            'segments' => []
        ];

        foreach ($matches as $match) {
            list($key,) = $this->splitSegment($match[3]);
            $result['pattern'] = str_replace($match[0], '{' . $key . '}', $result['pattern']);
            $result['segments']['{' . $key . '}'] = '{value}' . $match[4] . (isset($match[7]) ? $match[7] : '');
        }

        return $result;
    }

    /**
     * Splits segment into key - regexp part
     *
     * @param string $segment
     * @param string $default
     *
     * @return array
     */
    protected function splitSegment($segment, $default = '[a-z0-9-._]')
    {
        return strpos($segment, ':') === false ? [$segment, $default] : explode(':', $segment);
    }

    /**
     * Returns pattern
     *
     * @return string
     */
    public function pattern()
    {
        return $this->pattern;
    }

    /**
     * Returns controller
     *
     * @return string|callable
     */
    public function controller()
    {
        return $this->controller;
    }

    /**
     * Returns argument constraints
     *
     * @return array
     */
    public function requirements()
    {
        return $this->requirements;
    }

    /**
     * Sets values for each argument in pattern
     *
     * @param array $arguments
     *
     * @return array
     */
    public function arguments(array $arguments = [])
    {
        if (empty($arguments)) {
            return $this->arguments;
        }

        foreach ($arguments as $key => $value) {
            if (!isset($this->requirements[$key])) {
                $this->arguments[$key] = $value;
                continue;
            }

            $this->assertArgumentValue($key, $this->requirements[$key], $value);
            $this->arguments[$key] = $value;
        }

        return $this->arguments;
    }

    /**
     * Sets host requirement
     *
     * @param null|string $host
     *
     * @return string
     */
    public function host($host = null)
    {
        $this->host = empty($host) ? null : $host;

        return $this->host;
    }

    /**
     * Sets allowed schema
     *
     * @param string $schema
     *
     * @return string
     */
    public function schema($schema = null)
    {
        $this->schema = empty($schema) ? null : $schema;

        return $this->schema;
    }

    /**
     * Sets allowed methods
     *
     * @param array $methods
     *
     * @return array
     */
    public function methods(array $methods = [])
    {
        foreach ($methods as &$method) {
            $this->methods[] = strtoupper($method);
        }

        return $this->methods;
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
        return (
            $this->matchSchema($request->schema()) &&
            $this->matchMethods($request->method()) &&
            $this->matchHost($request->host()) &&
            $this->matchPath($request->path())
        );
    }

    /**
     * Returns true if request matches schema or if no schema restrictions set
     *
     * @param string $schema
     *
     * @return bool
     */
    protected function matchSchema($schema)
    {
        if (empty($this->schema)) {
            return true;
        }

        if (strpos($schema, $this->schema) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if request matches methods or if no methods restrictions set
     *
     * @param string $method
     *
     * @return bool
     */
    protected function matchMethods($method)
    {
        if (empty($this->methods)) {
            return true;
        }
        if (in_array($method, $this->methods)) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if request matches host or if no host restrictions set
     *
     * @param string $host
     *
     * @return bool
     */
    protected function matchHost($host)
    {
        if (empty($this->host)) {
            return true;
        }

        $host = preg_replace('/^[^:]+:\/\//i', '', $host);
        $regex = str_replace('\{basename\}', '.*', preg_quote($this->host));

        return preg_match('/^' . $regex . '$/i', $host);
    }

    /**
     * Returns true if request matches pattern
     *
     * @param string $path
     *
     * @return bool
     */
    protected function matchPath($path)
    {
        if (!preg_match_all($this->regex, $path, $matches, \PREG_SET_ORDER)) {
            return false;
        }

        foreach ($matches[0] as $k => $v) {
            if (is_numeric($k)) {
                continue;
            }

            $this->arguments[$k] = $v;
        }

        return true;
    }

    /**
     * Check if route should be used to make url
     *
     * @param string $controller
     * @param array  $arguments
     *
     * @return bool
     */
    public function check($controller, array $arguments = [])
    {
        if ($this->controller !== $controller) {
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
     * @param string $host
     * @param array  $arguments
     *
     * @return string
     * @throws RouteException
     */
    public function make($host, array $arguments = [])
    {
        return $host === null ? $this->makeRelative($arguments) : $this->makeAbsolute($host, $arguments);
    }

    /**
     * Creates relative url
     *
     * @param array $arguments
     *
     * @return string
     */
    protected function makeRelative(array $arguments = [])
    {
        $url = $url = $this->buildUrl($arguments);

        return './' . $url;
    }

    /**
     * Creates absolute url
     *
     * @param string $host
     * @param array  $arguments
     *
     * @return string
     */
    protected function makeAbsolute($host, array $arguments = [])
    {
        list($schema, $host) = $this->resolveHost($host);
        $url = $this->buildUrl($arguments);

        $regex = '/^' . str_replace('\{basename\}', '.*', preg_quote($this->host)) . '$/';
        if ($this->host && !preg_match($regex, $host)) {
            $host = str_replace('{basename}', $host, $this->host);
        }

        return ($schema ? $schema . '://' : null) . rtrim($host, '/') . '/' . $url;
    }

    /**
     * Resolves schema and host name with dir from passed basename
     *
     * @param string $host
     *
     * @return array
     */
    protected function resolveHost($host)
    {
        if (strpos($host, '://') !== false) {
            list($schema, $host) = explode('://', $host, 2);
        }

        if ($this->schema) {
            $schema = $this->schema;
        }

        if (empty($schema)) {
            $schema = 'http';
        }

        return array($schema, $host);
    }

    /**
     * Builds url with passed arguments
     *
     * @param array $arguments
     *
     * @return string
     */
    protected function buildUrl(array $arguments)
    {
        $url = [];
        foreach ($this->requirements as $key => $regex) {
            $this->assertArgumentRequirement($key, $regex, $arguments);

            if (array_key_exists($key, $arguments) && isset($this->builders['segments']['{' . $key . '}'])) {
                $this->assertArgumentValue($key, $regex, $arguments[$key]);
                $url['{' . $key . '}'] = str_replace('{value}', $this->strip($arguments[$key]), $this->builders['segments']['{' . $key . '}']);
            } else {
                $url['{' . $key . '}'] = substr($this->requirements[$key], -1) == '+' && isset($this->arguments[$key]) ? $this->arguments[$key] : null;
            }

            unset($arguments[$key]);
        }

        $url = strtr($this->builders['pattern'], $url);
        $url = str_replace('//', '/', $url);

        $query = array_filter($arguments);
        if (!empty($query)) {
            $url .= '?' . http_build_query($query, null, '&');
        }

        $url = ltrim($url, './');

        return $url;
    }

    /**
     * Asserts required argument matches regex
     *
     * @param string $key
     * @param string $regex
     * @param array  $arguments
     *
     * @throws RouteException
     */
    protected function assertArgumentRequirement($key, $regex, $arguments)
    {
        if (substr($regex, -1) === '+' && !array_key_exists($key, $arguments)) {
            throw new RouteException(sprintf('Missing value for argument "%s" in route "%s"', $key, $this->pattern));
        }
    }

    /**
     * Asserts argument value
     *
     * @param string $key
     * @param string $regex
     * @param mixed  $value
     *
     * @throws RouteException
     */
    protected function assertArgumentValue($key, $regex, $value)
    {
        if (!preg_match('/^' . $regex . '$/i', $value)) {
            throw new RouteException(sprintf('Invalid value for argument "%s" in route "%s", got "%s" need "/^%s\$/"', $key, $this->pattern, $value, $regex));
        }
    }

    /**
     * Strips string from non ASCII chars
     *
     * @param string $urlString string to strip
     * @param string $separator char replacing non ASCII chars
     *
     * @return string
     */
    protected function strip($urlString, $separator = '-')
    {
        if (is_numeric($urlString)) {
            return $urlString;
        }

        $urlString = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $urlString);
        $urlString = preg_replace('#[^\w \-\.]+#i', null, $urlString);
        $urlString = preg_replace('/[ -\.]+/', $separator, $urlString);
        $urlString = trim($urlString, '-.');

        return $urlString;
    }
}

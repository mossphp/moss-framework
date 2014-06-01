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

    protected $requirements = array();
    protected $constraints = array();
    protected $builders = array();
    protected $arguments = array();

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
    public function __construct($pattern, $controller, $arguments = array(), $methods = array())
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
    private function buildRegexp($pattern, array $matches)
    {
        $src = array();
        $trg = array();
        foreach ($matches as $match) {
            list($key, $regexp) = $this->splitSegment($match[3]);

            if (in_array(substr($regexp, -1), array('+', '*', '?'))) {
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
    private function buildConditionalSlash($pattern)
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
    private function buildRequirements(array $matches)
    {
        $result = array();
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
    private function buildBuilders($pattern, array $matches)
    {
        $result = array(
            'pattern' => $pattern,
            'segments' => array()
        );

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
    private function splitSegment($segment, $default = '[a-z0-9-._]')
    {
        return strpos($segment, ':') === false ? array($segment, $default) : explode(':', $segment);
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
     * @return string
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
     * @return $this
     */
    public function host($host = null)
    {
        $this->host = empty($host) ? null : $host;

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
    private function matchSchema($schema)
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
    private function matchMethods($method)
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
    private function matchHost($host)
    {
        if (empty($this->host)) {
            return true;
        }

        $regex = str_replace('{basename}', '.*', preg_quote($this->host));
        if (preg_match('/^' . $regex . '$/i', $host)) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if request matches pattern
     *
     * @param string $path
     *
     * @return bool
     */
    private function matchPath($path)
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
    public function check($controller, $arguments = array())
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
    public function make($host, $arguments = array())
    {
        $url = array();
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

        $schema = null;
        if (strpos($host, '://') !== false) {
            list($schema, $host) = explode('://', rtrim($host, '/'));
        }

        $regex = '/^' . str_replace('\{basename\}', '.*', preg_quote($this->host)) . '$/';
        if ($this->host && !preg_match($regex, $host)) {
            $host = str_replace('{basename}', $host, $this->host);
        }

        return ($schema ? $schema . '://' : null) . $host . '/' . $url;
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
    private function assertArgumentRequirement($key, $regex, $arguments)
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
    private function assertArgumentValue($key, $regex, $value)
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
    private function strip($urlString, $separator = '-')
    {
        $urlString = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $urlString);
        $urlString = strtolower($urlString);
        $urlString = preg_replace('#[^\w \-\.]+#i', null, $urlString);
        $urlString = preg_replace('/[ -\.]+/', $separator, $urlString);
        $urlString = trim($urlString, '-.');

        return $urlString;
    }
}

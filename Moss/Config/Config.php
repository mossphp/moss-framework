<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Config;

/**
 * Configuration representation
 *
 * @package Moss Config
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Config implements ConfigInterface
{
    protected $mode;
    protected $config = array(
        'framework' => array(
            'error' => array(
                'display' => true,
                'level' => -1,
                'detail' => true
            ),
            'session' => array(
                'name' => 'PHPSESSID',
                'cacheLimiter' => ''
            ),
            'cookie' => array(
                'domain' => null,
                'path' => '/',
                'http' => true,
                'ttl' => 2592000 // one month
            )
        ),
        'container' => array(),
        'dispatcher' => array(),
        'router' => array()
    );

    /**
     * Creates Config instance
     *
     * @param array  $arr
     * @param string $mode
     *
     * @throws ConfigException
     */
    public function __construct($arr = array(), $mode = null)
    {
        $this->mode($mode);
        $this->import($arr);
    }

    /**
     * Sets config mode
     *
     * @param null $mode
     *
     * @return string
     */
    public function mode($mode = null)
    {
        if ($mode !== null) {
            $this->mode = (string) $mode;
        }

        return $this->mode;
    }

    /**
     * Reads configuration properties from passed array
     *
     * @param array       $arr
     * @param null|string $prefix
     *
     * @return $this
     */
    public function import(array $arr, $prefix = null)
    {
        $importKeys = array();
        foreach ($arr as $key => $node) {
            if (strpos($key, 'import') === 0) {
                $mode = substr($key, 7);
                if ($mode == '' || $mode == $this->mode) {
                    $importKeys[] = $key;
                }

                continue;
            }

            switch ($key) {
                case 'container':
                    $node = $this->applyContainerDefaults($node);
                    break;
                case 'dispatcher':
                    $node = $this->applyDispatcherDefaults($node);
                    break;
                case 'router':
                    $node = $this->applyRouterDefaults($node);
                    break;
            }

            $this->config[$key] = array_merge($this->config[$key], $this->applyPrefix($node, $prefix));
        }

        foreach ($importKeys as $key) {
            foreach ($arr[$key] as $key => $value) {
                $this->import($value, $this->prefixKey($key, $prefix));
            }
        }

        return $this;
    }

    private function applyPrefix(array $array, $prefix = null)
    {
        if (!$this->checkPrefix($prefix)) {
            return $array;
        }

        $result = array();
        foreach ($array as $key => $value) {
            $result[$this->prefixKey($key, $prefix)] = $value;
        }

        return $result;
    }

    private function prefixKey($key, $prefix = null)
    {
        if (!$this->checkPrefix($prefix)) {
            return $key;
        }

        return $prefix . ':' . $key;
    }

    private function checkPrefix($prefix)
    {
        return !empty($prefix) && !is_numeric($prefix);
    }

    /**
     * Applies default values or missing properties for containers component definition
     *
     * @param array $array
     * @param array $defaults
     *
     * @return array
     */
    private function applyContainerDefaults(array $array, $defaults = array('shared' => false))
    {
        foreach ($array as &$node) {
            if (!is_array($node) || !array_key_exists('component', $node) || !is_callable($node['component'])) {
                continue;
            }

            $node = array_merge($defaults, $node);
            unset($node);
        }

        return $array;
    }

    /**
     * Applies default values or missing properties for event listener definition
     *
     * @param array $array
     *
     * @return array
     * @throws ConfigException
     */
    private function applyDispatcherDefaults(array $array)
    {
        foreach ($array as $evt) {
            foreach ($evt as $node) {
                if (!is_callable($node)) {
                    throw new ConfigException('Event listener must be callable, got ' . gettype($node));
                }
            }
        }

        return $array;
    }

    /**
     * Applies default values or missing properties for route definition
     *
     * @param array $array
     * @param array $defaults
     *
     * @return array
     * @throws ConfigException
     */
    private function applyRouterDefaults(array $array, $defaults = array('arguments' => array(), 'methods' => array()))
    {
        foreach ($array as &$node) {
            if (!isset($node['pattern'])) {
                throw new ConfigException('Missing required "pattern" property in route definition');
            }

            if (!isset($node['controller'])) {
                throw new ConfigException('Missing required "controller" property in route definition');
            }

            $node = array_merge($defaults, $node);
            unset($node);
        }

        return $array;
    }

    /**
     * Returns current stored configuration as array
     *
     * @return array
     */
    public function export()
    {
        return $this->config;
    }

    /**
     * Returns core variable value
     * If variable is undefined - returns false
     *
     * @param string $var     name of core variable
     * @param mixed  $default default value if variable not found
     *
     * @return mixed
     */
    public function get($var, $default = null)
    {
        return $this->getArrValue($this->config, $var, $default);
    }

    /**
     * Returns offset value from array or default value if offset does not exists
     *
     * @param array|\ArrayAccess $array
     * @param string             $offset
     * @param mixed              $default
     *
     * @return mixed
     */
    protected function getArrValue($array, $offset, $default = null)
    {
        $keys = explode('.', $offset);
        while ($i = array_shift($keys)) {
            if (!isset($array[$i])) {
                return $default;
            }

            $array = $array[$i];
        }

        return $array;
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $key
     *
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($key)
    {
        return isset($this->config[$key]);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $key
     *
     * @return mixed Can return all value types.
     */
    public function &offsetGet($key)
    {
        if (!isset($this->config[$key])) {
            $this->config[$key] = null;
        }

        return $this->config[$key];
    }

    /**
     * Offset to set
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if ($key === null) {
            array_push($this->config, $value);

            return;
        }

        $this->config[$key] = $value;
    }

    /**
     * Offset to unset
     *
     * @param mixed $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->config[$key]);
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count()
    {
        return count($this->config);
    }

    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->config);
    }

    /**
     * Return the key of the current element
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->config);
    }

    /**
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
        next($this->config);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->config);
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        $key = key($this->config);

        if ($key === false || $key === null) {
            return false;
        }

        return isset($this->config[$key]);
    }
}

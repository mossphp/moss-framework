<?php
namespace moss\config;

use moss\config\ConfigInterface;
use moss\config\ConfigException;


/**
 * Configuration representation
 *
 * @package Moss Config
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Config implements ConfigInterface
{

    protected $config = array(
        'framework' => array(
            'error' => array(
                'level' => -1,
                'detail' => true
            ),
            'session' => array(
                'host' => true,
                'ip' => true,
                'agent' => true,
                'salt' => null
            ),
            'cookie' => array(
                'domain' => null,
                'path' => '/',
                'http' => true
            )
        ),
        'namespaces' => array(),
        'container' => array(),
        'dispatcher' => array(),
        'router' => array()
    );

    /**
     * Creates Config instance
     *
     * @param array $arr
     *
     * @throws ConfigException
     */
    public function __construct($arr = array())
    {
        $this->import($arr);
    }

    /**
     * Reads configuration properties from passed array
     *
     * @param array $arr
     *
     * @return $this
     */
    public function import($arr)
    {
        foreach ($arr as $key => $node) {
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

            $this->config[$key] = array_merge($this->config[$key], $node);
        }

        return $this;
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
     * Applies default values or missing properties for containers component definition
     *
     * @param array $arr
     * @param array $defaults
     *
     * @return array
     */
    private function applyContainerDefaults(array $arr, $defaults = array('arguments' => array(), 'methods' => array(), 'shared' => false))
    {
        foreach ($arr as &$node) {
            if (!isset($node['class']) && !isset($node['closure'])) {
                continue;
            }

            $node = array_merge($defaults, $node);
            unset($node);
        }

        return $arr;
    }

    /**
     * Applies default values or missing properties for event listener definition
     *
     * @param array $arr
     * @param array $defaults
     *
     * @return array
     * @throws ConfigException
     */
    private function applyDispatcherDefaults(array $arr, $defaults = array('method' => null, 'arguments' => array()))
    {
        foreach ($arr as &$evt) {
            foreach ($evt as &$node) {
                if (!isset($node['component']) && !isset($node['closure'])) {
                    throw new ConfigException('Missing required "component" or "closure" property in event listener definition');
                }

                $node = array_merge($defaults, $node);
                unset($node);
            }
            unset($evt);
        }

        return $arr;
    }

    /**
     * Applies default values or missing properties for route definition
     *
     * @param array $arr
     * @param array $defaults
     *
     * @return array
     * @throws ConfigException
     */
    private function applyRouterDefaults(array $arr, $defaults = array('arguments' => array()))
    {
        foreach ($arr as &$node) {
            if (!isset($node['pattern'])) {
                throw new ConfigException('Missing required "pattern" property in route definition');
            }

            if (!isset($node['controller'])) {
                throw new ConfigException('Missing required "controller" property in route definition');
            }

            $node = array_merge($defaults, $node);

            unset($node);
        }

        return $arr;
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
     * @param array|\ArrayAccess $arr
     * @param string             $offset
     * @param mixed              $default
     *
     * @return mixed
     */
    protected function getArrValue($arr, $offset, $default = null)
    {
        $keys = explode('.', $offset);
        while ($i = array_shift($keys)) {
            if (!isset($arr[$i])) {
                return $default;
            }

            $arr = $arr[$i];
        }

        return $arr;
    }
}
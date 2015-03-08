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

use Moss\Bag\Bag;

/**
 * Configuration representation
 *
 * @package Moss Config
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Config extends Bag implements ConfigInterface
{
    protected $mode;
    protected $storage = [
        'framework' => [
            'error' => [
                'display' => true,
                'level' => -1,
                'detail' => true
            ],
            'session' => [
                'name' => 'PHPSESSID',
                'cacheLimiter' => ''
            ]
        ],
        'container' => [],
        'dispatcher' => [],
        'router' => []
    ];

    /**
     * Creates Config instance
     *
     * @param array  $arr
     * @param string $mode
     *
     * @throws ConfigException
     */
    public function __construct(array $arr = [], $mode = null)
    {
        $this->mode($mode);
        $this->import($arr);
    }

    /**
     * Sets config mode
     *
     * @param null|string $mode
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
        $importKeys = [];
        foreach ($arr as $key => $node) {
            if (strpos($key, 'import') === 0) {
                $mode = substr($key, 7);
                if ($mode == '' || $mode == $this->mode) {
                    $importKeys[] = $key;
                }

                continue;
            }

            $this->storage[$key] = array_merge($this->storage[$key], $this->applyPrefix($node, $prefix));
        }

        foreach ($importKeys as $key) {
            foreach ($arr[$key] as $key => $value) {
                $this->import($value, $this->prefixKey($key, $prefix));
            }
        }

        return $this;
    }

    /**
     * Applies prefix to array keys
     *
     * @param array $array
     * @param null|string  $prefix
     *
     * @return array
     */
    private function applyPrefix(array $array, $prefix = null)
    {
        if (!$this->checkPrefix($prefix)) {
            return $array;
        }

        $result = [];
        foreach ($array as $key => $value) {
            $result[$this->prefixKey($key, $prefix)] = $value;
        }

        return $result;
    }

    /**
     * Prefixes key
     *
     * @param string $key
     * @param null|string $prefix
     *
     * @return string
     */
    private function prefixKey($key, $prefix = null)
    {
        if (!$this->checkPrefix($prefix)) {
            return $key;
        }

        return $prefix . ':' . $key;
    }

    /**
     * Checks if key needs to be prefixed
     * Only strings are prefixed
     *
     * @param string $prefix
     *
     * @return bool
     */
    private function checkPrefix($prefix)
    {
        return !empty($prefix) && !is_numeric($prefix);
    }

    /**
     * Returns current stored configuration as array
     *
     * @return array
     */
    public function export()
    {
        return $this->storage;
    }
}

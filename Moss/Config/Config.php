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
    const PREFIX_GLUE = ':';

    protected $mode;
    protected $storage = [
        'framework' => [
            'error' => [
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
        $imports = [];
        foreach ($arr as $key => $node) {
            if (strpos($key, 'import') === 0) {
                $mode = substr($key, 7);
                if ($mode == '' || $mode == $this->mode) {
                    $imports = array_merge($imports, $node);
                }

                continue;
            }

            $this->storage[$key] = $this->merge($this->storage[$key], $this->applyPrefix($node, $prefix));
        }

        foreach ($imports as $node) {
            $this->import($node, $prefix);

        }

        return $this;
    }

    /**
     * Merges arrays without changing duplicated keys into arrays
     *
     * @param array $merged
     * @param array $array
     *
     * @return array
     */
    private function merge(array $merged, array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value) && isset ($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->merge($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Applies prefix to array keys
     *
     * @param array       $array
     * @param null|string $prefix
     *
     * @return array
     */
    private function applyPrefix(array $array, $prefix = null)
    {
        if ($prefix === null) {
            return $array;
        }

        $result = [];
        foreach ($array as $key => $value) {
            $result[$prefix . self::PREFIX_GLUE . $key] = $value;
        }

        return $result;
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

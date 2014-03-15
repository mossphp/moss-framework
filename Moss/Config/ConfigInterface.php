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
 * Config interface
 *
 * @package Moss Config
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface ConfigInterface
{

    /**
     * Reads configuration properties from passed array
     *
     * @param array $arr
     */
    public function import(array $arr);

    /**
     * Returns current stored configuration as array
     *
     * @return array
     */
    public function export();

    /**
     * Returns core variable value
     * If variable is undefined - returns false
     *
     * @param string $var
     *
     * @return mixed
     */
    public function get($var);
}

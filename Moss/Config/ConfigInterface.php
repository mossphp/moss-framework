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

use Moss\Bag\BagInterface;

/**
 * Config interface
 *
 * @package Moss Config
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface ConfigInterface extends BagInterface
{

    /**
     * Reads configuration properties from passed array
     *
     * @param array $arr
     *
     * @return ConfigInterface
     */
    public function import(array $arr);

    /**
     * Returns current stored configuration as array
     *
     * @return array
     */
    public function export();
}

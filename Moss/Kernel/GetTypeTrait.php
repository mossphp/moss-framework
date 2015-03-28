<?php

/*
* This file is part of the moss-framework package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Moss\Kernel;

/**
 * Moss kernel
 *
 * @package Moss Kernel
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
trait GetTypeTrait
{

    /**
     * Returns variable type or in case of objects, their class
     *
     * @param mixed $var
     *
     * @return string
     */
    protected function getType($var)
    {
        return is_object($var) ? get_class($var) : gettype($var);
    }
}

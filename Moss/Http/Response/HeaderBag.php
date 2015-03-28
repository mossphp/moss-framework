<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Http\Response;

use Moss\Bag\Bag;

/**
 * Response header bag
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class HeaderBag extends Bag
{
    /**
     * Builds array of headers
     *
     * @return array
     */
    public function asArray()
    {
        $headers = [];
        foreach (array_filter($this->storage) as $header => $value) {
            $headers[] = $header . ': ' . $value;
        }

        return $headers;
    }
}

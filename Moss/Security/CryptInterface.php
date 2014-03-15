<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Security;

/**
 * Security crypt interface
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface CryptInterface
{
    /**
     * Crypts passed string with set algorithm
     *
     * @param string $password
     *
     * @return string
     */
    public function hash($password);

    /**
     * Returns true if password and hashed are equal
     *
     * @param $password
     * @param $hashed
     *
     * @return bool
     */
    public function compare($password, $hashed);
}

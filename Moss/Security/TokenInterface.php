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
 * Security token
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface TokenInterface extends \Serializable
{
    /**
     * Returns set authentication credentials
     *
     * @return array
     */
    public function credentials();

    /**
     * Removes credentials
     *
     * @return $this
     */
    public function remove();

    /**
     * Returns true if token is authenticated
     *
     * @return bool
     */
    public function isAuthenticated();

    /**
     * Sets auth key
     *
     * @param null|string $auth
     *
     * @return string
     */
    public function authenticate($auth = null);

    /**
     * Sets user identifier
     *
     * @param null|int|string $user
     *
     * @return int|string
     */
    public function user($user = null);
}

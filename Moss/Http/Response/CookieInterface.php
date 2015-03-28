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

/**
 * Response cookie interface
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface CookieInterface
{
    /**
     * Returns cookie name
     *
     * @return string
     */
    public function name();

    /**
     * Returns cookie value.
     *
     * @return string
     */
    public function value();

    /**
     * Returns cookie domain
     *
     * @return null|string
     */
    public function domain();

    /**
     * Returns cookie TTL
     *
     * @return int
     */
    public function ttl();

    /**
     * Returns cookie path
     *
     * @return string
     */
    public function path();

    /**
     * Returns true whether cookie should only be sent over HTTPS from client
     *
     * @return bool
     */
    public function isSecure();

    /**
     * Returns true if cookie is accessible trough HTTP
     *
     * @return bool
     */
    public function isHttpOnly();

    /**
     * Returns true if this cookie will be cleared
     *
     * @return bool
     */
    public function isCleared();

    /**
     * Returns the cookie as a string.
     *
     * @return string
     */
    public function __toString();
}

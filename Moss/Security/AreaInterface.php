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

use Moss\Http\Request\RequestInterface;

/**
 * Secure area interface
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface AreaInterface
{
    /**
     * Returns pattern
     *
     * @return string
     */
    public function pattern();

    /**
     * Checks if identifier matches auth url
     * Returns true if matches
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function match(RequestInterface $request);

    /**
     * Returns array containing roles with access
     *
     * @return array
     */
    public function roles();

    /**
     * Returns array containing allowed IP addresses
     *
     * @return array
     */
    public function ips();

    /**
     * Returns true if use has access to area
     *
     * @param UserInterface $user
     * @param string        $ip
     *
     * @return bool
     */
    public function authorize(UserInterface $user, $ip = null);
}

<?php
namespace moss\security;

use moss\http\request\RequestInterface;

/**
 * Secure area interface
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface AreaInterface
{

    /**
     * Returns area pattern
     *
     * @return string
     */
    public function pattern();

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
     * Returns true if area matches request
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function match(RequestInterface $request);

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

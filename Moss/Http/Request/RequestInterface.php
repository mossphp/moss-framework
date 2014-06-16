<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Http\Request;

use Moss\Bag\BagInterface;
use Moss\Http\Cookie\CookieInterface;
use Moss\Http\Session\SessionInterface;

/**
 * Request representation
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface RequestInterface
{

    /**
     * Returns session value for given key or default if key does not exists
     *
     * @return SessionInterface
     */
    public function session();

    /**
     * Returns cookie value for given key or default if key does not exists
     *
     * @return CookieInterface
     */
    public function cookie();

    /**
     * Returns server param value for given key or default if key does not exists
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return null|string
     */
    public function server($key = null, $default = null);

    /**
     * Returns header value for given key or default if key does not exists
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return null|string
     */
    public function header($key = null, $default = null);

    /**
     * Returns query values bag
     *
     * @return BagInterface
     */
    public function query();

    /**
     * Returns post values bag
     *
     * @return BagInterface
     */
    public function body();

    /**
     * Returns files bag
     *
     * @return FilesBag
     */
    public function files();

    /**
     * Returns true if request is made via SSL
     *
     * @return bool
     */
    public function isSecure();

    /**
     * Returns true if request is made via XHR
     *
     * @return bool
     */
    public function isAjax();

    /**
     * Returns request method
     *
     * @return string
     */
    public function method();

    /**
     * Returns request protocol
     *
     * @return null|string
     */
    public function schema();

    /**
     * Returns requested host
     *
     * @return string
     */
    public function host();

    /**
     * Returns requested directory
     *
     * @return string
     */
    public function dir();

    /**
     * Returns requested path relative to script location
     *
     * @param bool $query
     *
     * @return string
     */
    public function path($query = false);

    /**
     * Returns requested base name (schema+host+dir)
     *
     * @param string $baseName
     *
     * @return string
     */
    public function baseName($baseName = null);

    /**
     * Returns requested URI
     *
     * @param bool $query
     *
     * @return string
     */
    public function uri($query = false);

    /**
     * Returns client IP address
     *
     * @return null|string
     */
    public function clientIp();

    /**
     * Returns requested controller identifier (if available)

     *
*@param string $route
     *
*@return null|string
     */
    public function route($route = null);

    /**
     * Returns address of page which referred user agent (if any)
     *
     * @return null|string
     */
    public function referrer();

    /**
     * Returns languages with quality order
     *
     * @return array
     */
    public function language();

    /**
     * Returns locale
     *
     * @param null|string $locale
     *
     * @return Request
     */
    public function locale($locale = null);

    /**
     * Returns requested format
     *
     * @param null|string $format
     *
     * @return string
     */
    public function format($format = null);
}

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
     * Returns bag with session properties
     *
     * @return SessionInterface
     */
    public function session();

    /**
     * Returns bag with cookie properties
     *
     * @return CookieInterface
     */
    public function cookie();

    /**
     * Returns bag with server properties
     *
     * @return BagInterface
     */
    public function server();

    /**
     * Returns bag with headers
     *
     * @return BagInterface
     */
    public function header();

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
     * Returns raw body content
     *
     * @return string
     */
    public function rawBody();

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
     * Returns requested route name (if successfully resolved)
     *
     * @param null|string $route
     *
     * @return string
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

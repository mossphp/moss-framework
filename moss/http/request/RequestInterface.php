<?php
namespace moss\http\request;

use moss\http\bag\BagInterface;
use moss\http\cookie\CookieInterface;
use moss\http\session\SessionInterface;

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
    public function post();

    /**
     * Returns files bag
     *
     * @return FilesBag|BagInterface
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
     * Returns requested base name (domain+directory)
     *
     * @param string $baseName
     *
     * @return string
     */
    public function baseName($baseName = null);

    /**
     * Returns client IP address
     *
     * @return null|string
     */
    public function clientIp();

    /**
     * Returns requested controller identifier (if available)
     *
     * @param string $controller
     *
     * @return null|string
     */
    public function controller($controller = null);

    /**
     * Returns requested URI
     *
     * @param bool $query
     *
     * @return string
     */
    public function uri($query = false);

    /**
     * Returns requested path relative to script location
     *
     * @param bool $query
     *
     * @return string
     */
    public function path($query = false);

    /**
     * Returns address of page which referred user agent (if any)
     *
     * @return null|string
     */
    public function referrer();

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
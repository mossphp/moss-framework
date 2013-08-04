<?php
namespace moss\http\request;

use moss\http\cookie\CookieInterface;
use moss\http\session\SessionInterface;

/**
 * Request representation
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface RequestInterface {

	/**
	 * Returns session value for given key or default if key does not exists
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return SessionInterface
	 */
	public function getSession($key = null, $default = null);

	/**
	 * Returns cookie value for given key or default if key does not exists
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return CookieInterface
	 */
	public function getCookie($key = null, $default = null);

	/**
	 * Returns server param value for given key or default if key does not exists
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return null|string
	 */
	public function getServer($key = null, $default = null);

	/**
	 * Returns header value for given key or default if key does not exists
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return null|string
	 */
	public function getHeader($key = null, $default = null);

	/**
	 * Returns query value for given key or default if key does not exists
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return null|string
	 */
	public function getQuery($key = null, $default = null);

	/**
	 * Returns post value for given key or default if key does not exists
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return null|string
	 */
	public function getPost($key = null, $default = null);

	/**
	 * Returns file value for given key or null if key does not exists
	 *
	 * @param string $key
	 *
	 * @return null|string
	 */
	public function getFile($key = null);

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
	 * Returns requested URL
	 *
	 * @return string
	 */
	public function url();

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
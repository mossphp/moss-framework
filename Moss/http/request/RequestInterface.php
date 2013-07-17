<?php
namespace Moss\http\request;

use Moss\http\cookie\CookieInterface;
use Moss\http\session\SessionInterface;

/**
 * Request representation
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface RequestInterface {

	/**
	 * Returns session instance
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return SessionInterface
	 */
	public function session($key, $value = null);

	/**
	 * Returns cookie instance
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return CookieInterface
	 */
	public function cookie($key, $value = null);

	/**
	 * Returns server param value for given key or null if key does not exists
	 *
	 * @param string $key
	 *
	 * @return null|string
	 */
	public function server($key);

	/**
	 * Returns header value for given key or null if key does not exists
	 *
	 * @param string $key
	 *
	 * @return null|string
	 */
	public function header($key);

	/**
	 * Returns query value for given key or null if key does not exists
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return null|string
	 */
	public function query($key, $value = null);

	/**
	 * Returns post value for given key or null if key does not exists
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return null|string
	 */
	public function post($key, $value = null);

	/**
	 * Returns file value for given key or null if key does not exists
	 *
	 * @param string $key
	 *
	 * @return null|string
	 */
	public function file($key);

	/**
	 * Returns true if request is made via XHR
	 *
	 * @return bool
	 */
	public function isXHR();

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
	public function referer();

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
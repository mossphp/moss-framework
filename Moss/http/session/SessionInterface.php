<?php
namespace moss\http\session;

/**
 * Session objects interface
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface SessionInterface extends \Iterator, \ArrayAccess, \Countable {

	/**
	 * Returns value for given key
	 *
	 * @param string $key
	 * @param string $default
	 *
	 * @return mixed
	 */
	public function get($key, $default = null);

	/**
	 * Sets value for given key
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @return $this
	 */
	public function set($key, $value = null);

	/**
	 * Retrieves all values as array
	 *
	 * @param array $headers
	 *
	 * @return array
	 */
	public function all($headers = array());

	/**
	 * Removes all values
	 *
	 * @return $this
	 */
	public function reset();
}
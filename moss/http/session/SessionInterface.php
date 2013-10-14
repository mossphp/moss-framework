<?php
namespace moss\http\session;

/**
 * Session objects interface
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface SessionInterface extends \Iterator, \ArrayAccess, \Countable
{
    /**
     * Regenerates the session ID
     *
     * @return $this
     */
    public function regenerate();

    /**
     * Clears all session data and regenerates session ID
     *
     * @return $this
     */
    public function invalidate();

    /**
     * Returns session identifier
     *
     * @param string $identifier
     *
     * @return string
     */
    public function identify($identifier = null);

    /**
     * Returns session name
     *
     * @param string $name
     *
     * @return string
     */
    public function name($name = null);

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
     * Removes value and key
     *
     * @param string $key
     *
     * @return $this
     */
    public function remove($key);

    /**
     * Retrieves all values as array
     *
     * @return array
     */
    public function all();

    /**
     * Removes all values
     *
     * @return $this
     */
    public function reset();
}
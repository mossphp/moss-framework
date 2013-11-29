<?php
namespace moss\http\session;

/**
 * Session object representation
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Session implements SessionInterface
{
    private $separator = '.';

    /**
     * Creates session wrapper instance
     * Also validates existing session - if session is invalid, resets it
     *
     * @param string $name
     * @param string $cacheLimiter
     */
    public function __construct($name = 'PHPSESSID', $cacheLimiter = '')
    {
        $this->name($name);
        $this->cacheLimiter($cacheLimiter);

        if (!$this->identify()) {
            $this->startSession();
        }
    }

    /**
     * Starts session
     *
     * @throws \RuntimeException
     */
    protected function startSession()
    {
        if (version_compare(phpversion(), '5.4.0', '>=') && \PHP_SESSION_ACTIVE === session_status()) {
            throw new \RuntimeException('Session already started by PHP.');
        }

        if (version_compare(phpversion(), '5.4.0', '<') && isset($_SESSION) && session_id()) {
            throw new \RuntimeException('Session already started by PHP ($_SESSION is set).');
        }

        if (ini_get('session.use_cookies') && headers_sent($file, $line)) {
            throw new \RuntimeException(sprintf('Unable to start session, headers have already been sent by "%s" at line %d.', $file, $line));
        }

        if (!session_start()) {
            throw new \RuntimeException('Unable to start session');
        }
    }

    /**
     * Clears all session data and regenerates session ID
     *
     * @return $this
     */
    public function invalidate()
    {
        $_SESSION = array();
        session_destroy();
        $this->startSession();
    }

    /**
     * Regenerates the session ID
     *
     * @return $this
     */
    public function regenerate()
    {
        session_regenerate_id(true);
        session_write_close();

        $backup = $_SESSION;
        $this->startSession();
        $_SESSION = $backup;

        return $this;
    }

    /**
     * Returns session identifier
     *
     * @param string $identifier
     *
     * @return string
     */
    public function identify($identifier = null)
    {
        if ($identifier !== null) {
            session_id($identifier);
        }

        return session_id();
    }

    /**
     * Returns session name
     *
     * @param string $name
     *
     * @return string
     */
    public function name($name = null)
    {
        if ($name !== null) {
            session_name($name);
        }

        return session_name();
    }

    /**
     * Returns session cache limiter
     *
     * @param string $cacheLimiter
     *
     * @return string
     */
    public function cacheLimiter($cacheLimiter = null)
    {
        if ($cacheLimiter !== null) {
            session_cache_limiter($cacheLimiter);
        }

        return session_cache_limiter();
    }

    /**
     * Returns value for given key
     *
     * @param string $key
     * @param string $default
     *
     * @return null|string
     */
    public function get($key = null, $default = null)
    {
        if ($key === null && $default === null) {
            return $this->all();
        }

        return $this->getFromArray($_SESSION, explode($this->separator, $key), $default);
    }

    /**
     * Sets value for given key
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function set($key, $value = null)
    {
        $this->putIntoArray($_SESSION, explode($this->separator, $key), $value);

        return $this;
    }

    /**
     * Removes value and key
     *
     * @param string $key
     *
     * @return $this
     */
    public function remove($key)
    {
        if (isset($_SESSION[$key])) {
            $_SESSION[$key] = null;
            unset($_SESSION[$key]);
        }

        return $this;
    }


    /**
     * Retrieves all values as array
     *
     * @return array
     */
    public function all()
    {
        $storage = $_SESSION;

        return $storage;
    }

    /**
     * Removes all values
     *
     * @return $this
     */
    public function reset()
    {
        foreach (array_keys($_SESSION) as $key) {
            unset($_SESSION[$key]);
        }

        return $this;
    }


    /**
     * Sets array elements value
     *
     * @param array  $array
     * @param string $keys
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function putIntoArray(&$array, $keys, $value)
    {
        $k = array_shift($keys);

        if (is_scalar($array)) {
            $array = (array) $array;
        }

        if (!isset($array[$k])) {
            $array[$k] = null;
        }

        if (empty($keys)) {
            return $array[$k] = & $value;
        }

        return $this->putIntoArray($array[$k], $keys, $value);
    }

    /**
     * Returns array element matching key
     *
     * @param array $arr
     * @param array $keys
     * @param mixed $default
     *
     * @return mixed
     */
    protected function getFromArray(&$arr, $keys, $default = null)
    {
        $key = array_shift($keys);

        if (!isset($arr[$key])) {
            return $default;
        }

        if (empty($keys)) {
            return $arr[$key];
        }

        return $this->getFromArray($arr[$key], $keys);
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $key
     *
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $key
     *
     * @return mixed Can return all value types.
     */
    public function &offsetGet($key)
    {
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = null;
        }

        return $_SESSION[$key];
    }

    /**
     * Offset to set
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if ($key === null) {
            array_push($_SESSION, $value);

            return;
        }

        $_SESSION[$key] = $value;
    }

    /**
     * Offset to unset
     *
     * @param mixed $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count()
    {
        return count($_SESSION);
    }

    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current()
    {
        reset($_SESSION);

        return array_shift($_SESSION);
    }

    /**
     * Return the key of the current element
     *
     * @return mixed
     */
    public function key()
    {
        return key($_SESSION);
    }

    /**
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
        reset($_SESSION);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void
     */
    public function rewind()
    {
        reset($_SESSION);
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        $key = key($_SESSION);

        if ($key === false || $key === null) {
            return false;
        }

        return isset($_SESSION[$key]);
    }
}

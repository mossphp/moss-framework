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
    private $storage;
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

        $this->storage = & $_SESSION;
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
        unset($this->storage);

        $_SESSION = array();
        session_destroy();
        $this->startSession();

        $this->storage = & $_SESSION;
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

        $this->storage = & $_SESSION;

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

        return $this->getFromArray($this->storage, explode($this->separator, $key), $default);
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
        $this->putIntoArray($this->storage, explode($this->separator, $key), $value);

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
        if (isset($this->storage[$key])) {
            $this->storage[$key] = null;
            unset($this->storage[$key]);
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
        $storage = $this->storage;

        return $storage;
    }

    /**
     * Removes all values
     *
     * @return $this
     */
    public function reset()
    {
        foreach (array_keys($this->storage) as $key) {
            unset($this->storage[$key]);
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
        return isset($this->storage[$key]);
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
        if (!isset($this->storage[$key])) {
            $this->storage[$key] = null;
        }

        return $this->storage[$key];
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
            array_push($this->storage, $value);

            return;
        }

        $this->storage[$key] = $value;
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
        unset($this->storage[$key]);
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count()
    {
        return count($this->storage) - 1;
    }

    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current()
    {
        reset($this->storage);

        return array_shift($this->storage);
    }

    /**
     * Return the key of the current element
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->storage);
    }

    /**
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
        reset($this->storage);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->storage);
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        $key = key($this->storage);

        if ($key === false || $key === null) {
            return false;
        }

        return isset($this->storage[$key]);
    }
}

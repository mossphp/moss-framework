<?php
namespace moss\http\session;

use moss\http\session\SessionInterface;

/**
 * Session object representation
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Session implements SessionInterface
{

    protected $authkey;

    private $storage;
    private $separator = '.';

    /**
     * Creates session wrapper instance
     * Also validates existing session - if session is invalid, resets it
     *
     * @param bool $agent
     * @param bool $ip
     * @param null $salt
     *
     * @throws \RuntimeException
     */
    public function __construct($agent = true, $ip = true, $salt = null)
    {
        $this->authkey = $this->authkey($agent, $ip, $salt);

        if (!$this->identify()) {
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

        $this->storage = & $_SESSION;

        if (!$this->validate()) {
            $this->invalidate();
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
        session_start();

        $this->storage = & $_SESSION;
        $this->storage['authkey'] = $this->authkey;
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
        session_start();
        $_SESSION = $backup;

        $this->storage = & $_SESSION;
        $this->storage['authkey'] = $this->authkey;

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
     * Validates session
     *
     * @return bool
     */
    public function validate()
    {
        return !empty($this->storage['authkey']) && $this->storage['authkey'] === $this->authkey;
    }

    /**
     * Generates session auth key
     *
     * @param bool $agent
     * @param bool $ip
     * @param bool $salt
     *
     * @return string
     */
    protected function authkey($agent, $ip, $salt)
    {
        $authkey = array();

        if ($agent) {
            $authkey[] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'UndefinedUserAgent';
        }

        if ($ip) {
            if (!empty($_SERVER['REMOTE_ADDR'])) {
                $authkey[] = $_SERVER['REMOTE_ADDR'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $authkey[] = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $authkey[] = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $authkey[] = 'UnknownIp';
            }
        }

        return hash('sha512', implode($authkey) . $salt, false);
    }

    /**
     * Generates salt
     *
     * @param int $length
     *
     * @return string
     */
    protected function generateSalt($length)
    {
        $r = array();
        for ($i = 0; $i < $length; ++$i) {
            $r[] = pack('S', mt_rand(0, 0xffff));
        }

        return substr(base64_encode(implode($r)), 0, 2);
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
            unset($this->storage[$key]);
        }

        return $this;
    }


    /**
     * Retrieves all values as array
     *
     * @param array $params
     *
     * @return array
     */
    public function all($params = array())
    {
        if (!empty($params)) {
            $this->storage = array();

            foreach ($params as $key => $value) {
                $this->putIntoArray($this->storage, explode($this->separator, $key), $value);
            }
        }

        return $this->storage;
    }

    /**
     * Removes all values
     *
     * @return $this
     */
    public function reset()
    {
        return $this;
    }


    /**
     * Sets array elements value
     *
     * @param array  $arr
     * @param string $keys
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function putIntoArray(&$arr, $keys, $value)
    {
        $k = array_shift($keys);

        if (is_scalar($arr)) {
            $arr = (array) $arr;
        }

        if (!isset($arr[$k])) {
            $arr[$k] = null;
        }

        if (empty($keys)) {
            return $arr[$k] = & $value;
        }

        return $this->putIntoArray($arr[$k], $keys, $value);
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
        $k = array_shift($keys);

        if (!isset($arr[$k])) {
            return $default;
        }

        if (empty($keys)) {
            return $arr[$k];
        }

        return $this->getFromArray($arr[$k], $keys);
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

        while ($key !== null && $key !== 'authkey') {
            $this->next();
            $key = key($this->storage);
        }

        if ($key === false || $key === null) {
            return false;
        }

        return isset($this->storage[$key]);
    }
}
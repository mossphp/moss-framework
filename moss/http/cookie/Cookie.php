<?php
namespace moss\http\cookie;

/**
 * Cookie object representation
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Cookie implements CookieInterface
{

    protected $domain;
    protected $path;
    protected $expire;
    protected $secure = null;
    protected $httponly = true;

    protected $protected = array('__utma', '__utmz', 'PHPSESSID');

    private $storage;
    private $separator = '.';

    /**
     * Creates cookie wrapper instance
     *
     * @param string $domain
     * @param string $path
     * @param bool   $httponly
     * @param int    $ttl
     */
    public function __construct($domain = null, $path = '/', $httponly = true, $ttl = 5356800)
    {
        if ($domain === null) {
            $domain = empty($_SERVER['HTTP_HOST']) ? null : $_SERVER['HTTP_HOST'];
        }

        $this->domain = $domain;
        $this->path = $path;
        $this->httponly = $httponly;
        $this->expire = microtime(true) + $ttl;

        $this->storage = & $_COOKIE;
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
        if (isset($_COOKIE[$key])) {
            unset($_COOKIE[$key]);
        }

        setcookie($key, "", time() - 3600, $this->path, $this->domain, $this->secure, $this->httponly);

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
        foreach (array_keys($_COOKIE) as $key) {
            $_COOKIE = array();
            setcookie($key, "", time() - 3600, $this->path, $this->domain, $this->secure, $this->httponly);
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
            return $array[$k] = $value;
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
        if (empty($key)) {
            $key = array_push($this->storage, $value);
        } else {
            $this->storage[$key] = $value;
        }

        setcookie($key, $value, $this->expire, $this->path, $this->domain, $this->secure, $this->httponly);
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
        setcookie($key, "", time() - 3600, $this->path, $this->domain, $this->secure, $this->httponly);
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count()
    {
        $count = count($this->storage) - count(array_intersect_key($this->storage, $this->protected));

        return $count < 0 ? 0 : $count;
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
     * @return boolean
     */
    public function valid()
    {
        $key = key($this->storage);

        while ($key !== null && in_array($key, $this->protected)) {
            $this->next();
            $key = key($this->storage);
        }

        if ($key === false || $key === null) {
            return false;
        }

        return isset($this->storage[$key]);
    }
}

<?php
namespace Moss\Http\Cookie;

use Moss\Http\Bag\Bag;

/**
 * Cookie object representation
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Cookie extends Bag implements CookieInterface
{
    protected $domain;
    protected $path;
    protected $expire;
    protected $secure = null;
    protected $httponly = true;

    protected $storage;

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
     * Removes offset from bag
     * If no offset set, removes all values
     *
     * @param string $offset attribute to remove from
     *
     * @return $this
     */
    public function remove($offset = null)
    {
        if ($offset === null) {
            $this->reset();

            return $this;
        }

        if (isset($this->storage[$offset])) {
            unset($this->storage[$offset]);
        }

        $this->setcookie($offset, "", time() - 3600);

        return $this;
    }

    /**
     * Removes all options
     *
     * @return $this
     */
    public function reset()
    {
        foreach (array_keys($this->storage) as $key) {
            $this->storage = array();
            $this->setcookie($key, "", time() - 3600);
        }

        return $this;
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

        $this->setcookie($key, $value, $this->expire);
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
        $this->setcookie($key, "", time() - 3600);
    }

    /**
     * Executes setcookie
     *
     * @param string $name
     * @param string $value
     * @param int    $expire
     */
    protected function setcookie($name, $value, $expire = 0)
    {
        setcookie($name, $value, $expire, $this->path, $this->domain, $this->secure, $this->httponly);
    }
}

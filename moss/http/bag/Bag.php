<?php
namespace moss\http\bag;

/**
 * Parameter bag
 *
 * @package  Moss HTTP
 * @author   Michal Wachowski <wachowski.michal@gmail.com>
 */
class Bag implements BagInterface
{
    protected $storage = array();

    /**
     * Construct
     *
     * @param array $storage
     */
    public function __construct($storage = array())
    {
        $this->all($storage);
    }


    /**
     * Retrieves offset value
     *
     * @param string $offset
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($offset = null, $default = null)
    {
        if ($offset === null) {
            return $this->all();
        }

        return $this->getFromArray($this->storage, explode(self::SEPARATOR, $offset), $default);
    }

    /**
     * Sets value to offset
     *
     * @param string $offset
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($offset, $value = null)
    {
        $this->setIntoArray($this->storage, explode(self::SEPARATOR, $offset), $value);

        return $this;
    }

    /**
     * Returns true if offset exists in bag
     *
     * @param string $offset
     *
     * @return bool
     */
    public function has($offset = null)
    {
        if ($offset === null) {
            return $this->count() > 0;
        }

        $offset = explode(self::SEPARATOR, $offset);

        if (count($offset) > 1) {
            $arr = & $this->getFromArray($this->storage, array_slice($offset, 0, -1), false);
        } else {
            $arr = & $this->storage;
        }

        $offset = array_slice($offset, -1);
        $offset = reset($offset);

        return is_array($arr) ? array_key_exists($offset, $arr) : false;
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

        $offset = explode(self::SEPARATOR, $offset);

        if (count($offset) > 1) {
            $arr = & $this->getFromArray($this->storage, array_slice($offset, 0, -1), false);
        } else {
            $arr = & $this->storage;
        }

        $offset = array_slice($offset, -1);
        $offset = reset($offset);

        if (is_array($arr) && array_key_exists($offset, $arr)) {
            unset($arr[$offset]);
        }

        return $this;
    }

    /**
     * Returns all options
     * If array passed, becomes bag content
     *
     * @param array $array overwrites values
     *
     * @return array
     */
    public function all($array = array())
    {
        if ($array !== array()) {
            $this->reset();

            foreach ((array) $array as $key => $value) {
                $this->setIntoArray($this->storage, explode(self::SEPARATOR, $key), $value);
            }
        }

        return $this->storage;
    }

    /**
     * Removes all options
     *
     * @return $this
     */
    public function reset()
    {
        $this->storage = array();

        return $this;
    }

    /**
     * Returns array element matching key
     *
     * @param array  $arr
     * @param array  $keys
     * @param string $default
     *
     * @return string
     */
    protected function & getFromArray(&$arr, $keys, $default = null)
    {
        $key = array_shift($keys);
        if (!isset($arr[$key])) {
            return $default;
        }

        if (empty($keys)) {
            return $arr[$key];
        }

        return $this->getFromArray($arr[$key], $keys, $default);
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
    protected function setIntoArray(&$array, $keys, $value)
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

        return $this->setIntoArray($array[$k], $keys, $value);
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
        return count($this->storage);
    }

    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->storage);
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
        next($this->storage);
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
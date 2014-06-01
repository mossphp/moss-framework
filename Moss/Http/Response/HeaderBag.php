<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Http\Response;

use Moss\Http\Bag\BagInterface;

/**
 * Response header bag
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 * @todo - move all header related methods from request here and extend Bag
 */
class HeaderBag implements BagInterface
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

        return isset($this->storage[$offset]) ? $this->storage[$offset] : $default;
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
        $this->storage[$offset] = $value;

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
            return !empty($this->storage);
        }

        return isset($this->storage[$offset]);
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
                $this->set($key, $value);
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

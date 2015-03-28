<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Bag;

/**
 * Parameter bag
 *
 * @package  Moss HTTP
 * @author   Michal Wachowski <wachowski.michal@gmail.com>
 */
class Bag extends AbstractBag implements BagInterface
{
    protected $storage = [];

    /**
     * Construct
     *
     * @param array $storage
     */
    public function __construct(array $storage = [])
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
        if ($offset === null) {
            array_push($this->storage, $value);

            return $this;
        }

        if (is_array($offset)) {
            foreach ($offset as $key => $value) {
                $this->storage[$key] = $value;
            }

            return $this;
        }

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

        $arr = &$this->getArrayByReference($offset);

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

        $arr = &$this->getArrayByReference($offset);

        if (is_array($arr) && array_key_exists($offset, $arr)) {
            unset($arr[$offset]);
        }

        return $this;
    }

    /**
     * Returns reference to sub array
     *
     * @param string $offset
     *
     * @return mixed
     */
    protected function & getArrayByReference(&$offset)
    {
        $offset = explode(self::SEPARATOR, $offset);

        if (count($offset) > 1) {
            $arr = &$this->getFromArray($this->storage, array_slice($offset, 0, -1), false);
        } else {
            $arr = &$this->storage;
        }

        $offset = array_slice($offset, -1);
        $offset = reset($offset);

        return $arr;
    }

    /**
     * Returns all elements
     * If array passed, becomes bag content
     *
     * @param array $array overwrites values
     *
     * @return array
     */
    public function all(array $array = [])
    {
        if ($array !== []) {
            $this->storage = $array;
        }

        return $this->storage;
    }

    /**
     * Returns array element matching key
     *
     * @param array $array
     * @param array $keys
     * @param mixed $default
     *
     * @return string
     */
    protected function & getFromArray(&$array, $keys, $default = null)
    {
        $key = array_shift($keys);
        if (!isset($array[$key])) {
            return $default;
        }

        if (empty($keys)) {
            return $array[$key];
        }

        return $this->getFromArray($array[$key], $keys, $default);
    }

    /**
     * Sets array elements value
     *
     * @param array $array
     * @param array $keys
     * @param mixed $value
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
            return $array[$k] = &$value;
        }

        return $this->setIntoArray($array[$k], $keys, $value);
    }
}

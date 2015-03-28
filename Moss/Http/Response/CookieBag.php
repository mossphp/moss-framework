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

use Moss\Bag\Bag;

/**
 * Response cookie bag
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class CookieBag extends Bag
{
    /**
     * @var array|CookieInterface[]
     */
    protected $storage = [];

    /**
     * Returns true if bag has cookie with set name
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

        return array_key_exists($offset, $this->storage);
    }

    /**
     * Retrieves offset value
     *
     * @param string          $offset
     * @param CookieInterface $default
     *
     * @return mixed
     */
    public function get($offset = null, $default = null)
    {
        $this->assertInstance($default);

        return parent::get($offset, $default);
    }

    /**
     * Sets value to offset
     *
     * @param string|CookieInterface $offset
     * @param CookieInterface        $value
     *
     * @return $this
     */
    public function set($offset, $value = null)
    {
        if ($value === null) {
            $this->assertInstance($offset);
        } else {
            $this->assertInstance($value);
        }

        if ($offset instanceof CookieInterface) {
            $value = $offset;
            $offset = $offset->name();
        }

        return parent::set($offset, $value);
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

        unset($this->storage[$offset]);

        return $this;
    }

    /**
     * Returns all options
     * If array passed, becomes bag content
     *
     * @param array $array overwrites values
     *
     * @return array|CookieInterface[]
     */
    public function all(array $array = [])
    {
        if ($array !== []) {
            foreach ($array as $cookie) {
                $this->set($cookie);
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
        $this->storage = [];

        return $this;
    }

    /**
     * Builds array of cookie strings
     *
     * @return array
     */
    public function asArray()
    {
        $cookies = [];
        foreach (array_filter($this->storage) as $cookie) {
            $cookies[] = 'Set-Cookie: ' . (string) $cookie;
        }

        return $cookies;
    }

    /**
     * Offset to set
     *
     * @param mixed           $key
     * @param CookieInterface $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->assertInstance($value);

        if ($key === null) {
            $this->storage[$value->name()] = $value;

            return;
        }

        $this->storage[$key] = $value;
    }

    /**
     * Asserts if passed value is of proper instance
     *
     * @param mixed $value
     */
    protected function assertInstance($value)
    {
        if ($value !== null && !$value instanceof CookieInterface) {
            throw new \InvalidArgumentException('Value must be an instance of CookieInterface');
        }
    }
}

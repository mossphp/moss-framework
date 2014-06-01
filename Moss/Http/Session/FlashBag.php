<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Http\Session;

/**
 * Session flash bag
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class FlashBag implements FlashBagInterface
{

    /**
     * @var SessionInterface
     */
    private $session;
    private $prefix;

    /**
     * Constructor
     * Binds flashbag with container array
     *
     * @param SessionInterface $session
     * @param string           $prefix
     */
    public function __construct(SessionInterface $session = null, $prefix = 'FlashBag')
    {
        if ($session === null) {
            $session = & $_SESSION;
        }

        $this->session = & $session;
        $this->prefix = $prefix;
        if (!isset($this->session[$this->prefix])) {
            $this->session[$this->prefix] = array();
        }
    }

    /**
     * Removes all messages from container
     *
     * @return $this
     */
    public function reset()
    {
        $this->session[$this->prefix] = array();

        return $this;
    }

    /**
     * Adds message to flashbag
     *
     * @param string $message
     * @param string $type
     *
     * @return $this
     */
    public function add($message, $type = 'error')
    {
        $this->session[$this->prefix][] = array('message' => $message, 'type' => $type);

        return $this;
    }

    /**
     * Returns true if at least one message of set type exists
     *
     * @param null|string $type
     *
     * @return bool
     */
    public function has($type = null)
    {
        if (!$type) {
            return !empty($this->session[$this->prefix]);
        }

        foreach ($this->session[$this->prefix] as $message) {
            if ($message['type'] === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns array containing messages of set type
     *
     * @param null|string $type
     *
     * @return array
     */
    public function get($type = null)
    {
        $result = array();

        foreach ($this->session[$this->prefix] as $i => $message) {
            if ($type === null || $message['type'] === $type) {
                $result[] = $message;
                unset($this->session[$this->prefix][$i]);
            }
        }

        return $result;
    }

    /**
     * Returns next message
     *
     * @return mixed
     */
    public function retrieve()
    {
        return array_shift($this->session[$this->prefix]);
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset
     *
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($offset)
    {
        return isset($this->session[$this->prefix][$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        if (!isset($this->session[$this->prefix][$offset])) {
            return null;
        }

        $result = $this->session[$this->prefix][$offset];
        unset($this->session[$this->prefix][$offset]);

        return $result;
    }

    /**
     * Offset to set
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $offset = count($this->session[$this->prefix]);
        }

        $this->session[$this->prefix][$offset] = $value;
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->session[$this->prefix][$offset]);
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count()
    {
        return count($this->session[$this->prefix]);
    }


    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current()
    {
        reset($this->session[$this->prefix]);

        return array_shift($this->session[$this->prefix]);
    }

    /**
     * Return the key of the current element
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->session[$this->prefix]);
    }

    /**
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
        reset($this->session[$this->prefix]);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->session[$this->prefix]);
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean
     */
    public function valid()
    {
        return reset($this->session[$this->prefix]);
    }
}

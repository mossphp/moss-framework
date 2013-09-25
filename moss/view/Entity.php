<?php
namespace moss\view;

/**
 * Moss view
 * Array access prototype to grants read-only access to entity properties
 *
 * @package Moss View
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
abstract class Entity implements \ArrayAccess
{

    /**
     * Whether a offset exists
     *
     * @param mixed $offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (!isset($this->$offset)) {
            return null;
        }

        return $this->$offset;
    }

    /**
     * Offset to set
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @throws \BadMethodCallException
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException(sprintf('Only read is permited for entity %s', get_class($this)));
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset
     *
     * @throws \BadMethodCallException
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException(sprintf('Only read is permited for entity %s', get_class($this)));
    }

}
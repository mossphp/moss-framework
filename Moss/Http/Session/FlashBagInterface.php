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
 * Session flash bag interface
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface FlashBagInterface extends \Iterator, \ArrayAccess, \Countable
{

    /**
     * Removes all messages from container
     *
     * @return FlashBagInterface
     */
    public function reset();

    /**
     * Adds message to flashbag
     *
     * @param string $message
     * @param string $type
     *
     * @return $this
     */
    public function add($message, $type = 'error');

    /**
     * Returns true if at least one message of set type exists
     *
     * @param null|string $type
     *
     * @return bool
     */
    public function has($type = null);

    /**
     * Returns array containing messages of set type
     * Removes message after returning it
     *
     * @param null|string $type
     *
     * @return array
     */
    public function get($type = null);

    /**
     * Returns next message
     * Removes message after returning it
     *
     * @return mixed
     */
    public function retrieve();
}

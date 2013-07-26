<?php
namespace Moss\http\session;

/**
 * Session flash bag interface
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface FlashBagInterface extends \Iterator, \ArrayAccess, \Countable {

	/**
	 * Removes all messages from container
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
	 *
	 * @param null|string $type
	 *
	 * @return array
	 */
	public function get($type = null);

	/**
	 * Returns next message
	 *
	 * @return mixed
	 */
	public function retrieve();
}
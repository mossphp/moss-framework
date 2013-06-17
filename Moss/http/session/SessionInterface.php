<?php
namespace Moss\http\session;

/**
 * Session objects interface
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface SessionInterface extends \Iterator, \ArrayAccess, \Countable {

	/**
	 * Creates session wrapper instance
	 */
	public function __construct();

	/**
	 * Clears session data
	 *
	 * @return SessionInterface
	 */
	public function reset();
}
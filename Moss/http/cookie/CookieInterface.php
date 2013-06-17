<?php
namespace Moss\http\cookie;

/**
 * Cookie objects interface
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface CookieInterface extends \Iterator, \ArrayAccess, \Countable {

	/**
	 * Clears all cookie data
	 *
	 * @return CookieInterface
	 */
	public function reset();
}
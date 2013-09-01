<?php
namespace moss\security;

/**
 * Security token stash interface
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface TokenStashInterface {

	/**
	 * Stashes token
	 *
	 * @param TokenInterface $Token
	 *
	 * @return $this
	 */
	public function put(TokenInterface $Token);

	/**
	 * Returns stashed token
	 *
	 * @return TokenInterface
	 */
	public function get();

	/**
	 * Destroys stashed token
	 *
	 * @return $this
	 */
	public function destroy();
}
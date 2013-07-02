<?php
namespace Moss\security;

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
	public function stash(TokenInterface $Token);

	/**
	 * Returns stashed token
	 *
	 * @return TokenInterface
	 */
	public function get();
}
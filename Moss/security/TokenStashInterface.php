<?php
namespace Moss\security;


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
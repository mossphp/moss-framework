<?php
namespace moss\security;

use moss\security\AuthenticationException;
use moss\security\UserInterface;
use moss\http\request\RequestInterface;

/**
 * Security interface
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface SecurityInterface {

	/**
	 * Authenticates token in authentication providers
	 *
	 * @return $this
	 * @throws AuthenticationException
	 */
	public function authenticate();

	/**
	 * Checks if authenticated user has access to requested area
	 *
	 * @param RequestInterface $Request
	 *
	 * @return $this
	 * @throws AuthorizationException
	 */
	public function authorize(RequestInterface $Request);

	/**
	 * Returns token stash
	 *
	 * @return TokenStashInterface
	 */
	public function stash();

	/**
	 * Returns token
	 *
	 * @return TokenInterface
	 */
	public function token();

	/**
	 * Returns authenticated user instance from user providers
	 *
	 * @return UserInterface
	 */
	public function user();

	/**
	 * Returns url (or null if not set) on which user should be redirected if has no access
	 *
	 * @return null|string
	 */
	public function loginUrl();
}
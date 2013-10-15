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
interface SecurityInterface
{

    /**
     * Adds user provider
     *
     * @param UserProviderInterface $Provider
     *
     * @return $this
     */
    public function registerUserProvider(UserProviderInterface $Provider);

    /**
     * Registers secure area in security
     *
     * @param AreaInterface $Area
     *
     * @return $this
     */
    public function registerArea(AreaInterface $Area);

    /**
     * Creates token from credentials via user providers
     *
     * @param array $credentials
     *
     * @return $this
     * @throws AuthenticationException
     */
    public function tokenize(array $credentials);

    /**
     * Authenticates token in user providers for requested area
     *
     * @param RequestInterface $Request
     *
     * @return $this
     * @throws AuthenticationException
     */
    public function authenticate(RequestInterface $Request);

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
     * Destroys authenticated user, logs out
     *
     * @return $this
     */
    public function destroy();

    /**
     * Returns url (or null if not set) on which user should be redirected if has no access
     *
     * @return null|string
     */
    public function loginUrl();
}
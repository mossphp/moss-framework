<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Security;

use Moss\Http\Request\RequestInterface;

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
     * @param UserProviderInterface $provider
     *
     * @return $this
     */
    public function registerUserProvider(UserProviderInterface $provider);

    /**
     * Registers secure area in security
     *
     * @param AreaInterface $area
     *
     * @return $this
     */
    public function registerArea(AreaInterface $area);

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
     * @param RequestInterface $request
     *
     * @return $this
     * @throws AuthenticationException
     */
    public function authenticate(RequestInterface $request);

    /**
     * Checks if authenticated user has access to requested area
     *
     * @param RequestInterface $request
     *
     * @return $this
     * @throws AuthorizationException
     */
    public function authorize(RequestInterface $request);

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

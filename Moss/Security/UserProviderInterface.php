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

/**
 * Security user provider
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface UserProviderInterface
{

    /**
     * Returns true if provider can handle credentials
     *
     * @param array $credentials
     *
     * @return bool
     */
    public function supportsCredentials(array $credentials);

    /**
     * Creates token from credentials
     *
     * @param array $credentials
     *
     * @return $this
     */
    public function tokenize(array $credentials);

    /**
     * Returns true if provider can handle token
     *
     * @param TokenInterface $token
     *
     * @return bool
     */
    public function supportsToken(TokenInterface $token);

    /**
     * Authenticates token in provider
     *
     * @param TokenInterface $token
     *
     * @return bool
     */
    public function authenticate(TokenInterface $token);

    /**
     * Returns user instance matching token
     *
     * @param TokenInterface $token
     *
     * @return UserInterface
     */
    public function get(TokenInterface $token);
}

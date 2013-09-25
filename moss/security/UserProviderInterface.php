<?php
namespace moss\security;

use moss\security\TokenInterface;
use moss\security\UserInterface;

/**
 * Security user provider
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface UserProviderInterface
{

    /**
     * Returns true if provider can handle token
     *
     * @param TokenInterface $Token
     *
     * @return bool
     */
    public function supports(TokenInterface $Token);

    /**
     * Authenticates token and sets user identifier in token
     *
     * @param TokenInterface $Token
     *
     * @return bool|UserInterface
     * @throws AuthenticationException
     */
    public function authenticate(TokenInterface $Token);

    /**
     * Returns user instance matching user token
     *
     * @param TokenInterface $Token
     *
     * @return UserInterface
     * @throws AuthenticationException
     */
    public function get(TokenInterface $Token);

    /**
     * Updates user data in providers storage
     *
     * @param TokenInterface $Token
     *
     * @return $this
     */
    public function refresh(TokenInterface $Token);
}
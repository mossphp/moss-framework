<?php
namespace moss\security;

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
     * Creates token from credentials via user providers
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
     * Authenticates token and sets user identifier in token
     * Removes used credentials from token
     *
     * @param TokenInterface $token
     *
     * @return bool
     * @throws AuthenticationException
     */
    public function authenticate(TokenInterface $token);

    /**
     * Returns user instance matching user token
     *
     * @param TokenInterface $token
     *
     * @return UserInterface
     * @throws AuthenticationException
     */
    public function get(TokenInterface $token);

    /**
     * Updates user data in providers storage
     *
     * @param TokenInterface $token
     *
     * @return $this
     */
    public function refresh(TokenInterface $token);
}

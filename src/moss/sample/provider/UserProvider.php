<?php
namespace moss\sample\provider;

use moss\sample\entity\User;
use moss\security\AuthenticationException;
use moss\security\Token;
use moss\security\TokenInterface;
use moss\security\UserInterface;
use moss\security\UserProviderInterface;

class UserProvider implements UserProviderInterface
{

    private $users = array(
        'login' => 'password'
    );

    /**
     * Returns true if provider can handle credentials
     *
     * @param array $credentials
     *
     * @return bool
     */
    public function supportsCredentials(array $credentials)
    {
        return array_keys($credentials) === array('login', 'password');
    }

    /**
     * Creates token from credentials via user providers
     *
     * @param array $credentials
     *
     * @return $this
     */
    public function tokenize(array $credentials)
    {
        if (!isset($credentials['login']) || !isset($credentials['password'])) {
            return false;
        }

        if (!isset($this->users[$credentials['login']])) {
            return false;
        }

        if ($this->users[$credentials['login']] !== $credentials['password']) {
            return false;
        }

        return new Token($credentials['login'] . 'AuthKey', $credentials['login']);
    }

    /**
     * Returns true if provider can handle token
     *
     * @param TokenInterface $token
     *
     * @return bool
     */
    public function supportsToken(TokenInterface $token)
    {
        return true;
    }

    /**
     * Authenticates token and sets user identifier in token
     * Removes used credentials from token
     *
     * @param TokenInterface $token
     *
     * @return bool
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$token->isAuthenticated()) {
            return false;
        }

        return isset($this->users[$token->user()]);
    }

    /**
     * Returns user instance matching user token
     *
     * @param TokenInterface $token
     *
     * @return UserInterface
     * @throws AuthenticationException
     */
    public function get(TokenInterface $token)
    {
        if (!$token->isAuthenticated()) {
            throw new AuthenticationException('Unable to retrieve user for unauthenticated token');
        }

        return new User($token->user(), array('role1', 'role2'), array('right1', 'right2', 'right3'));
    }

    /**
     * Updates user data in providers storage
     *
     * @param TokenInterface $token
     *
     * @return $this
     */
    public function refresh(TokenInterface $token)
    {
        return;
    }
} 
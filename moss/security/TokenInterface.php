<?php
namespace moss\security;

/**
 * Security token
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface TokenInterface extends \Serializable
{

    /**
     * Returns set authentication credentials
     *
     * @param int|string $offset
     *
     * @return array
     */
    public function credentials($offset = null);

    /**
     * Removes credentials
     *
     * @param int|string $offset
     *
     * @return $this
     */
    public function remove($offset = null);

    /**
     * Returns true if token is authenticated
     *
     * @return bool
     */
    public function isAuthenticated();

    /**
     * Sets auth key
     *
     * @param null|string $auth
     *
     * @return string
     */
    public function authenticate($auth = null);

    /**
     * Sets user identifier
     *
     * @param null|int|string $user
     *
     * @return int|string
     */
    public function user($user = null);
}
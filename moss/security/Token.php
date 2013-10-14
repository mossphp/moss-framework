<?php
namespace moss\security;

use moss\security\TokenInterface;

/**
 * Security token
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Token implements TokenInterface
{

    private $credentials = array();
    private $auth;
    private $user;

    /**
     * Constructor
     *
     * @param null|string $login
     * @param null|string $password
     */
    public function __construct($login = null, $password = null)
    {
        $this->credentials = array('login' => $login, 'password' => $password);
    }

    /**
     * Returns set authentication credentials
     *
     * @param int|string $offset
     *
     * @return mixed
     */
    public function credentials($offset = null)
    {
        if ($offset === null) {
            return $this->credentials;
        }

        if (!isset($this->credentials[$offset])) {
            return null;
        }

        return $this->credentials[$offset];
    }

    /**
     * Removes credentials
     *
     * @param int|string $offset
     *
     * @return $this
     */
    public function remove($offset = null)
    {
        if ($offset === null) {
            $this->credentials = array();
            return $this;
        }

        if (!isset($this->credentials[$offset])) {
            return $this;
        }

        unset($this->credentials[$offset]);

        return $this;
    }


    /**
     * Returns true if token is authenticated
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this->auth !== null;
    }

    /**
     * Sets auth key
     *
     * @param null|string $auth
     *
     * @return string
     */
    public function authenticate($auth = null)
    {
        if ($auth !== null) {
            $this->credentials = array();
            $this->auth = $auth;
        }

        return $this->auth;
    }

    /**
     * Sets user identifier
     *
     * @param null|int|string $user
     *
     * @return int|string
     */
    public function user($user = null)
    {
        if ($user !== null) {
            $this->user = $user;
        }

        return $this->user;
    }


    /**
     * String representation of object
     *
     * @return string
     */
    public function serialize()
    {
        return serialize(array($this->auth, $this->user));
    }

    /**
     * Constructs the object
     *
     * @param string $serialized
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        $arr = unserialize($serialized);
        $this->auth = $arr[0];
        $this->user = $arr[1];
    }
}
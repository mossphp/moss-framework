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
 * Security token
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Token implements TokenInterface
{

    private $auth;
    private $user;

    /**
     * Constructor
     *
     * @param string $auth
     * @param mixed  $user
     */
    public function __construct($auth = null, $user = null)
    {
        $this->auth = $auth;
        $this->user = $user;
    }

    /**
     * Returns set authentication credentials
     *
     * @return array
     */
    public function credentials()
    {
        return [
            'auth' => $this->auth,
            'user' => $this->user
        ];
    }

    /**
     * Removes credentials
     *
     * @return $this
     */
    public function remove()
    {
        $this->auth = null;
        $this->user = null;

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
        return serialize([$this->auth, $this->user]);
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
        list($this->auth, $this->user) = unserialize($serialized);
    }
}

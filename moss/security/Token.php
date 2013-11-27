<?php
namespace moss\security;

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
    private $realm;

    /**
     * Constructor
     *
     * @param string     $auth
     * @param int|string $user
     * @param string     $realm
     */
    public function __construct($auth = null, $user = null, $realm = null)
    {
        $this->auth = $auth;
        $this->user = $user;
        $this->realm = $realm;
    }

    /**
     * Returns realm to which the token belongs
     *
     * @return string
     */
    public function realm()
    {
        return $this->realm;
    }


    /**
     * Returns set authentication credentials
     *
     * @return array
     */
    public function credentials()
    {
        return array(
            'auth' => $this->auth,
            'user' => $this->user
        );
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
        return json_encode(array($this->auth, $this->user, $this->realm));
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
        list($this->auth, $this->user, $this->realm) = json_decode($serialized, true);
    }
}

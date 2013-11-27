<?php
namespace moss\security;

/**
 * Security protected area
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Area extends Pattern implements AreaInterface
{
    protected $roles;
    protected $access;
    protected $ips;

    /**
     * Creates ACL area instance
     *
     * @param string $pattern pattern matching blocked controller identifier
     * @param array  $roles
     * @param array  $ips
     */
    public function __construct($pattern, $roles = array(), $ips = array())
    {
        $this->regexp = $this->buildRegExp($pattern);
        $this->roles = (array) $roles;
        $this->ips = (array) $ips;

        parent::__construct($pattern);
    }

    /**
     * Returns array containing roles with access
     *
     * @return array
     */
    public function roles()
    {
        return $this->roles;
    }

    /**
     * Returns array containing allowed IP addresses
     *
     * @return array
     */
    public function ips()
    {
        return $this->ips;
    }

    /**
     * Returns true if use has access
     *
     * @param UserInterface $user
     * @param string        $ip
     *
     * @return bool
     */
    public function authorize(UserInterface $user, $ip = null)
    {
        return $this->authRoles($user) && $this->authIps($ip);
    }

    /**
     * Returns true if use has role to access area
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    protected function authRoles(UserInterface $user)
    {
        if (empty($this->roles)) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if user has IP to access area
     *
     * @param string $userIp
     *
     * @return bool
     */
    protected function authIps($userIp)
    {
        if (empty($this->ips)) {
            return true;
        }

        foreach ($this->ips as $ip) {
            if ($userIp === $ip) {
                return true;
            }
        }

        return false;
    }
}

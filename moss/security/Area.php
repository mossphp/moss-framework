<?php
namespace moss\security;

use moss\http\request\RequestInterface;
use moss\security\AreaInterface;

/**
 * Security protected area
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Area implements AreaInterface
{

    protected $pattern;
    protected $regexp;
    protected $roles;
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
        $this->pattern = $pattern;
        $this->regexp = $this->buildRegExp($pattern);
        $this->roles = (array) $roles;
        $this->ips = (array) $ips;
    }

    /**
     * Builds regular expression
     *
     * @param string $pattern
     *
     * @return string
     */
    protected function buildRegExp($pattern)
    {
        preg_match_all('#([^:]+)#m', $pattern, $patternMatches);

        foreach ($patternMatches[1] as &$match) {
            if (strpos($match, '*') !== false) {
                $match = str_replace('*', '[^:]+', $match);
            }

            if (strpos($match, '!') === 0) {
                $match = '.*(?<!' . substr($match, 1) . ')';
            }

            unset($match);
        }

        $pattern = str_replace($patternMatches[0], $patternMatches[1], $pattern);
        $pattern = str_replace('\\', '\\\\', $pattern);
        $pattern = '/^' . $pattern . '$/';
        return $pattern;
    }

    /**
     * Returns area pattern
     *
     * @return string
     */
    public function pattern()
    {
        return $this->pattern;
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
     * Checks if identifier matches secure area
     * Returns true if matches
     *
     * @param RequestInterface $Request
     *
     * @return bool
     */
    public function match(RequestInterface $Request)
    {
        if (preg_match($this->regexp, $Request->controller())) {
            return true;
        }

        return false;
    }


    /**
     * Returns true if use has access
     *
     * @param UserInterface $User
     * @param string        $ip
     *
     * @return bool
     */
    public function authorize(UserInterface $User, $ip = null)
    {
        return $this->authRole($User) && $this->authIp($ip);
    }

    /**
     * Returns true if use has role to access area
     *
     * @param UserInterface $User
     *
     * @return bool
     */
    protected function authRole(UserInterface $User)
    {
        if (empty($this->roles)) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($User->hasRole($role)) {
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
    public function authIp($userIp)
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
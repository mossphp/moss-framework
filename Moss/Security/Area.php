<?php
namespace Moss\Security;

use \Moss\Http\Request\RequestInterface;

/**
 * Security protected area
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Area implements AreaInterface
{
    const ANY = '[^:]+';

    protected $pattern;
    protected $regex;

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
        $this->pattern = $pattern;
        $this->regex = $this->buildRegExp($pattern);

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
        preg_match_all('#(' . self::ANY . ')#m', $pattern, $patternMatches);

        foreach ($patternMatches[1] as &$match) {
            if (strpos($match, '*') !== false) {
                $match = str_replace('*', self::ANY, $match);
            }

            if (strpos($match, '!') === 0) {
                $match = self::ANY . '(?<!' . substr($match, 1) . ')';
            }

            unset($match);
        }

        $pattern = str_replace($patternMatches[0], $patternMatches[1], $pattern);
        $pattern = '/^' . $pattern . '$/';

        return $pattern;
    }

    /**
     * Returns auth url pattern
     *
     * @return string
     */
    public function pattern()
    {
        return $this->pattern;
    }

    /**
     * Checks if identifier matches auth url
     * Returns true if matches
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function match(RequestInterface $request)
    {
        if (preg_match($this->regex, $request->controller())) {
            return true;
        }

        return false;
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

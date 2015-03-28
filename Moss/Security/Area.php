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

use \Moss\Http\Request\RequestInterface;

/**
 * Security protected area
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Area implements AreaInterface
{
    const ANY = '[^\/]+';

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
        $pattern = '/' . trim($pattern, '/');
        $pattern = str_replace('/', '\/', $pattern);
        preg_match_all('#((\\\/)?[^\\\/]+)#im', $pattern, $patternMatches);

        foreach ($patternMatches[1] as &$match) {
            if (strpos($match, '*') !== false) {
                $match = str_replace('*', self::ANY, $match);
            }

            if (preg_match('#\(![^\)]+\)#i', $match)) {
                $match = preg_replace_callback(
                    '/^(.*)\(!([^\)]+)\)$/i',
                    array($this, 'buildRegExpCallback'),
                    $match,
                    \PREG_SET_ORDER
                );
            }

            unset($match);
        }

        $pattern = str_replace($patternMatches[0], $patternMatches[1], $pattern);
        $pattern = '/^' . $pattern . '\/?.*$/i';

        return $pattern;
    }

    /**
     * Regexp callback
     *
     * @param string $match
     *
     * @return string
     */
    protected function buildRegExpCallback($match)
    {
        return $match[1] . ($match[1] == '\/' ? '?' : '') . '(?!.*\b(' . $match[2] . ')\b).*';
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
     * Checks if path matches auth url
     * Returns true if matches
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function match(RequestInterface $request)
    {
        return (bool) preg_match($this->regex, $request->path());
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

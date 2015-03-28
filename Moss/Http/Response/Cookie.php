<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Http\Response;

/**
 * Response cookie
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Cookie implements CookieInterface
{
    protected $name;
    protected $value;
    protected $domain;
    protected $ttl;
    protected $path;
    protected $secure;
    protected $httpOnly;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $value
     * @param int    $ttl
     * @param string $path
     * @param string $domain
     * @param bool   $secure
     * @param bool   $httpOnly
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($name, $value = null, $ttl = 2592000, $path = '/', $domain = null, $secure = false, $httpOnly = true)
    {
        // from PHP source code
        $this->name($name);
        $this->value($value);
        $this->domain($domain);
        $this->ttl($ttl);
        $this->path(empty($path) ? '/' : $path);
        $this->secure($secure);
        $this->httpOnly($httpOnly);
    }

    /**
     * Returns cookie name
     *
     * @param string $name
     *
     * @return string
     */
    public function name($name = null)
    {
        if ($name !== null) {
            if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
                throw new \InvalidArgumentException(sprintf('Cookie name "%s" contains invalid characters', $name));
            }

            if (empty($name)) {
                throw new \InvalidArgumentException('Cookie name cannot be empty');
            }

            $this->name = $name;
        }

        return $this->name;
    }

    /**
     * Returns cookie value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function value($value = null)
    {
        if ($value !== null) {
            $this->value = $value;
        }

        return (string) $this->value;
    }

    /**
     * Returns cookie domain
     *
     * @param string $domain
     *
     * @return string
     */
    public function domain($domain = null)
    {
        if ($domain !== null) {
            $this->domain = $domain;
        }

        return (string) $this->domain;
    }

    /**
     * Returns cookie TTL
     *
     * @param int $ttl
     *
     * @return int
     */
    public function ttl($ttl = null)
    {
        if ($ttl === null) {
            return (int) $this->ttl;
        }

        if ($ttl instanceof \DateTime) {
            return $this->ttl = $ttl->format('U');
        }

        return $this->ttl = time() + (int) $ttl;
    }

    /**
     * Returns cookie path
     *
     * @param string $path
     *
     * @return string
     */
    public function path($path = null)
    {
        if ($path !== null) {
            $this->path = $path;
        }

        return (string) $this->path;
    }

    /**
     * Sets if cookie is secure
     *
     * @param bool $secure
     *
     * @return int
     */
    public function secure($secure = null)
    {
        if ($secure !== null) {
            $this->secure = (bool) $secure;
        }

        return (bool) $this->secure;
    }

    /**
     * Returns true whether cookie should only be sent over HTTPS from client
     *
     * @return bool
     */
    public function isSecure()
    {
        return $this->secure();
    }

    /**
     * Sets if cookie is http only
     *
     * @param bool $httpOnly
     *
     * @return bool
     */
    public function httpOnly($httpOnly = null)
    {
        if ($httpOnly !== null) {
            $this->httpOnly = (bool) $httpOnly;
        }

        return (bool) $this->httpOnly;
    }

    /**
     * Returns true if cookie is accessible trough HTTP
     *
     * @return bool
     */
    public function isHttpOnly()
    {
        return $this->httpOnly();
    }

    /**
     * Returns true if this cookie will be cleared
     *
     * @return bool
     */
    public function isCleared()
    {
        return $this->ttl < time();
    }

    /**
     * Returns the cookie as a string.
     *
     * @return string The cookie
     */
    public function __toString()
    {
        $str = urlencode($this->name()) . '=';

        if ($this->value() === '') {
            $str .= 'deleted; expires=' . gmdate('r', time() - 31536001);
        } else {
            $str .= urlencode($this->value());

            if ($this->ttl() !== 0) {
                $str .= '; expires=' . gmdate('r', $this->ttl());
            }
        }

        $str .= '; path=' . $this->path;

        if ($this->domain()) {
            $str .= '; domain=' . $this->domain();
        }

        if (true === $this->isSecure()) {
            $str .= '; secure';
        }

        if (true === $this->isHttpOnly()) {
            $str .= '; httponly';
        }

        return $str;
    }
}

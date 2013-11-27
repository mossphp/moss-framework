<?php
namespace moss\security;

use moss\http\request\RequestInterface;

/**
 * Security facade
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Security implements SecurityInterface
{

    /** @var TokenStashInterface */
    protected $stash;

    /** @var UserInterface */
    protected $user;

    /** @var array|UserProviderInterface[] */
    protected $providers = array();

    /** @var array|AreaInterface[] */
    protected $areas = array();

    /** @var array|FormUrlInterface[] */
    protected $urls = array();

    /**
     * Constructor
     *
     * @param TokenStashInterface $stash
     */
    public function __construct(TokenStashInterface $stash)
    {
        $this->stash = & $stash;
    }

    /**
     * Adds user provider
     *
     * @param UserProviderInterface $provider
     *
     * @return $this
     */
    public function registerUserProvider(UserProviderInterface $provider)
    {
        $this->providers[] = & $provider;

        return $this;
    }

    /**
     * Registers secure area in security
     *
     * @param AreaInterface $area
     *
     * @return $this
     */
    public function registerArea(AreaInterface $area)
    {
        $this->areas[] = & $area;

        return $this;
    }

    /**
     * Registers auth url in security

     *
*@param FormUrlInterface $url

     *
*@return $this
     */
    public function registerAuthUrl(FormUrlInterface $url)
    {
        $this->urls[] = & $url;

        return $this;
    }

    /**
     * Creates token from credentials via user providers
     *
     * @param array $credentials
     *
     * @return $this
     * @throws AuthenticationException
     */
    public function tokenize(array $credentials)
    {
        if (empty($credentials)) {
            throw new AuthenticationException('Unable to tokenize, empty credentials');
        }

        foreach ($this->providers as $provider) {
            if (!$provider->supportsCredentials($credentials)) {
                continue;
            }

            if (!$token = $provider->tokenize($credentials)) {
                $this->stash()
                     ->destroy();
                throw new AuthenticationException(sprintf('Credentials could not be tokenized in provider "%s", destroying token', get_class($provider)));
            }

            $this->stash()
                 ->put($token);

            return $this;
        }

        $this->stash()
             ->destroy();
        throw new AuthenticationException(sprintf('Missing provider supporting credentials "%s", destroying token', implode(', ', array_keys($credentials))));
    }


    /**
     * Authenticates token in user providers for requested area
     *
     * @param RequestInterface $request
     *
     * @return $this
     * @throws AuthenticationException
     */
    public function authenticate(RequestInterface $request)
    {
        $token = $this->token();

        if (!$token && !$this->findMatchingArea($request)) {
            return $this;
        }

        if (!$token) {
            throw new AuthenticationException('Unable to authenticate, token is missing');
        }

        foreach ($this->providers as $provider) {
            if (!$provider->supportsToken($token)) {
                continue;
            }

            if (!$provider->authenticate($token)) {
                $this->stash()
                     ->destroy();
                throw new AuthenticationException(sprintf('Token could not be authenticated in provider "%s", destroying token', get_class($provider)));
            }

            $this->user = $provider->get($token);

            return $this;
        }

        $this->stash()
             ->destroy();
        throw new AuthenticationException(sprintf('Missing provider supporting token "%s", destroying token', get_class($token)));
    }

    /**
     * Checks if authenticated user has access to requested area
     *
     * @param RequestInterface $request
     *
     * @return $this
     * @throws AuthorizationException
     */
    public function authorize(RequestInterface $request)
    {
        if (!$area = $this->findMatchingArea($request)) {
            return $this;
        }

        if (!$this->user) {
            throw new AuthorizationException(sprintf('Access denied to area "%s". No authenticated user', $area->pattern()));
        }

        if (!$area->authorize($this->user, $request->clientIp())) {
            throw new AuthorizationException(sprintf('Access denied to area "%s". Authenticated user does not have access', $area->pattern()));
        }

        return $this;
    }

    /**
     * Returns matching area or null if not found
     *
     * @param RequestInterface $request
     *
     * @return AreaInterface|null
     */
    protected function findMatchingArea(RequestInterface $request)
    {
        foreach ($this->areas as $area) {
            if (!$area->match($request)) {
                continue;
            }

            return $area;
        }

        return null;
    }

    /**
     * Returns token stash
     *
     * @return TokenStashInterface
     */
    public function stash()
    {
        return $this->stash;
    }

    /**
     * Returns token
     *
     * @return TokenInterface
     */
    public function token()
    {
        return $this->stash->get();
    }

    /**
     * Returns authenticated user instance from user providers
     *
     * @return UserInterface
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * Destroys authenticated user, logs out
     *
     * @return $this
     */
    public function destroy()
    {
        $this->user = null;
        $this->stash->destroy();
    }


    /**
     * Returns url (or null if not set) on which user should be redirected if has no access
     *
     * @param RequestInterface $request
     *
     * @return null|string
     */
    public function authUrl(RequestInterface $request)
    {
        foreach ($this->urls as $url) {
            if ($url->match($request)) {
                return $url->url();
            }
        }

        return null;
    }
}

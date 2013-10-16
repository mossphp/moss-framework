<?php
namespace moss\security;

use moss\security\SecurityInterface;
use moss\security\TokenStashInterface;
use moss\security\UserProviderInterface;
use moss\security\UserInterface;
use moss\security\AuthenticationException;
use moss\security\AuthorizationException;
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
    protected $Stash;

    protected $loginUrl;

    /** @var UserInterface */
    protected $User;

    /** @var array|UserProviderInterface[] */
    protected $Providers = array();

    /** @var array|AreaInterface[] */
    protected $Areas = array();

    /**
     * Constructor
     *
     * @param TokenStashInterface $Stash
     * @param string              $loginUrl
     */
    public function __construct(TokenStashInterface $Stash, $loginUrl = null)
    {
        $this->Stash = & $Stash;
        $this->loginUrl = $loginUrl;
    }

    /**
     * Adds user provider
     *
     * @param UserProviderInterface $Provider
     *
     * @return $this
     */
    public function registerUserProvider(UserProviderInterface $Provider)
    {
        $this->Providers[] = & $Provider;

        return $this;
    }

    /**
     * Registers secure area in security
     *
     * @param AreaInterface $Area
     *
     * @return $this
     */
    public function registerArea(AreaInterface $Area)
    {
        $this->Areas[] = & $Area;

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

        foreach ($this->Providers as $Provider) {
            if (!$Provider->supportsCredentials($credentials)) {
                continue;
            }

            if (!$Token = $Provider->tokenize($credentials)) {
                throw new AuthenticationException(sprintf('Credentials could not be tokenized in provider "%s".', get_class($Provider)));
            }

            $this->stash()->put($Token);

            return $this;
        }

        throw new AuthenticationException(sprintf('Missing provider supporting credentials "%s"', implode(', ', array_keys($credentials))));
    }


    /**
     * Authenticates token in user providers for requested area
     *
     * @param RequestInterface $Request
     *
     * @return $this
     * @throws AuthenticationException
     */
    public function authenticate(RequestInterface $Request)
    {
        if(!$this->findMatchingArea($Request)) {
            return $this;
        }

        if (!$Token = $this->token()) {
            throw new AuthenticationException('Unable to authenticate, token is missing');
        }

        foreach ($this->Providers as $Provider) {
            if (!$Provider->supportsToken($Token)) {
                continue;
            }

            if (!$Provider->authenticate($Token)) {
                throw new AuthenticationException(sprintf('Token could not be authenticated in provider "%s".', get_class($Provider)));
            }

            $this->User = $Provider->get($Token);

            return $this;
        }

        throw new AuthenticationException(sprintf('Missing provider supporting token "%s"', get_class($Token)));
    }

    /**
     * Checks if authenticated user has access to requested area
     *
     * @param RequestInterface $Request
     *
     * @return $this
     * @throws AuthorizationException
     */
    public function authorize(RequestInterface $Request)
    {
        if(!$Area = $this->findMatchingArea($Request)) {
            return $this;
        }

        if (!$this->User) {
            throw new AuthorizationException(sprintf('Access denied to area "%s". No authenticated user', $Area->pattern()));
        }

        if (!$Area->authorize($this->User, $Request->clientIp())) {
            throw new AuthorizationException(sprintf('Access denied to area "%s". Authenticated user does not have access', $Area->pattern()));
        }

        return $this;
    }

    /**
     * Returns matching area or null if not found
     *
     * @param RequestInterface $Request
     *
     * @return AreaInterface|null
     */
    protected function findMatchingArea(RequestInterface $Request) {
        foreach ($this->Areas as $Area) {
            if (!$Area->match($Request)) {
                continue;
            }

            return $Area;
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
        return $this->Stash;
    }

    /**
     * Returns token
     *
     * @return TokenInterface
     */
    public function token()
    {
        return $this->Stash->get();
    }

    /**
     * Returns authenticated user instance from user providers
     *
     * @return UserInterface
     */
    public function user()
    {
        return $this->User;
    }

    /**
     * Destroys authenticated user, logs out
     *
     * @return $this
     */
    public function destroy()
    {
        $this->User = null;
        $this->Stash->destroy();
    }


    /**
     * Returns url (or null if not set) on which user should be redirected if has no access
     *
     * @return null|string
     */
    public function loginUrl()
    {
        return $this->loginUrl;
    }
}
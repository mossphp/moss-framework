<?php
namespace moss\security;

use moss\security\SecurityInterface;
use moss\security\TokenStashInterface;
use moss\security\UserProviderInterface;
use moss\security\UserInterface;
use moss\security\AuthenticationException;
use moss\security\TokenException;
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
     * Authenticates token in authentication providers
     *
     * @param RequestInterface $Request
     *
     * @return $this
     * @throws AuthenticationException
     */
    public function authenticate(RequestInterface $Request)
    {
        foreach ($this->Areas as $Area) {
            if (!$Area->match($Request)) {
                continue;
            }

            if (!$Token = $this->token()) {
                throw new TokenException('Unable to authenticate, token is missing');
            }

            foreach ($this->Providers as $Provider) {
                if (!$Provider->supports($Token)) {
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

        return $this;
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
        foreach ($this->Areas as $Area) {
            if (!$Area->match($Request)) {
                continue;
            }

            if (!$this->User) {
                throw new AuthorizationException(sprintf('Access denied to area "%s". No authenticated user', $Area->pattern()));
            }

            if (!$Area->authorize($this->User, $Request->clientIp())) {
                throw new AuthorizationException(sprintf('Access denied to area "%s". Authenticated user does not have access', $Area->pattern()));
            }

            return $this;
        }

        return $this;
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
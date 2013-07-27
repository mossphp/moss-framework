<?php
namespace Moss\security;

use Moss\security\SecurityInterface;
use Moss\security\TokenStashInterface;
use Moss\security\UserProviderInterface;
use Moss\security\UserInterface;
use Moss\security\AuthenticationException;
use Moss\security\AuthorizationException;
use Moss\http\request\RequestInterface;

/**
 * Security facade
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Security implements SecurityInterface {

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
	 * @param string $loginUrl
	 */
	public function __construct(TokenStashInterface $Stash, $loginUrl = null) {
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
	public function registerUserProvider(UserProviderInterface $Provider) {
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
	public function registerArea(AreaInterface $Area) {
		$this->Areas[] = & $Area;

		return $this;
	}

	/**
	 * Authenticates token in authentication providers
	 *
	 * @return $this
	 * @throws AuthenticationException
	 */
	public function authenticate() {
		$Token = $this->Stash->get();
		foreach($this->Providers as $Provider) {
			if(!$Provider->supports($Token)) {
				continue;
			}

			if(!$this->User = $Provider->authenticate($Token)) {
				throw new AuthenticationException('Unable to authenticate token. Invalid or incomplete data.');
			}

			return $this;
		}

		throw new AuthenticationException('Token was not authenticated. Missing provider supporting token');
	}

	/**
	 * Checks if authenticated user has access to requested area
	 *
	 * @param RequestInterface $Request
	 *
	 * @return $this
	 * @throws AuthorizationException
	 */
	public function authorize(RequestInterface $Request) {
		foreach($this->Areas as $Area) {
			if(!$Area->match($Request)) {
				continue;
			}

			if(!$Area->authorize($this->User, $Request->clientIp())) {
				throw new AuthorizationException('Access denied to area ' . $Area->pattern());
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
	public function stash() {
		return $this->Stash;
	}

	/**
	 * Returns token
	 *
	 * @return TokenInterface
	 */
	public function token() {
		return $this->Stash->get();
	}

	/**
	 * Returns authenticated user instance from user providers
	 *
	 * @return UserInterface
	 */
	public function user() {
		return $this->User;
	}

	/**
	 * Returns url (or null if not set) on which user should be redirected if has no access
	 *
	 * @return null|string
	 */
	public function loginUrl() {
		return $this->loginUrl;
	}
}
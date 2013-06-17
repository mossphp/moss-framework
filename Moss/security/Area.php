<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Michal
 * Date: 18.06.13
 * Time: 01:04
 * To change this template use File | Settings | File Templates.
 */

namespace Moss\security;

use Moss\http\request\RequestInterface;
use \Moss\security\AreaInterface;

class Area implements AreaInterface {

	/**
	 * Creates ACL area instance
	 *
	 * @param string $pattern pattern matching blocked controller identifier
	 * @param array  $roles
	 * @param array  $ips
	 * @param string $redirect
	 */
	public function __construct($pattern, $roles = array(), $ips = array(), $redirect = null) {
		$this->pattern = $pattern;
		$this->regexp = $this->buildRegExp($pattern);
		$this->roles = $roles;
		$this->ips = $ips;
		$this->redirect = $redirect;
	}

	/**
	 * Builds regular expression
	 *
	 * @param string $pattern
	 *
	 * @return string
	 */
	protected function buildRegExp($pattern) {
		preg_match_all('#([^:]+)#m', $pattern, $patternMatches);

		foreach($patternMatches[1] as &$match) {
			if(strpos($match, '*') !== false) {
				$match = str_replace('*', '[^:]+', $match);
			}

			if(strpos($match, '!') === 0) {
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
	public function pattern() {
		return $this->pattern;
	}


	/**
	 * Returns array containing roles with access
	 *
	 * @return array
	 */
	public function roles() {
		return $this->roles;
	}


	/**
	 * Returns array containing allowed IP addresses
	 *
	 * @return array
	 */
	public function ips() {
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
	public function match(RequestInterface $Request) {
		if(preg_match($this->pattern, $Request->controller())) {
			return true;
		}

		return false;
	}

	/**
	 * Returns true if use has access to area
	 *
	 * @param UserInterface $User
	 *
	 * @return bool
	 */
	public function authorizeUser(UserInterface $User) {
		if(empty($this->roles)) {
			return true;
		}

		foreach($this->roles as $role) {
			if($User->hasRole($role)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns true if IP may access area
	 *
	 * @param string $ip
	 *
	 * @return bool
	 */
	public function authorizeIp($ip) {
		if(empty($this->ips)) {
			return true;
		}

		foreach($this->ips as $ip) {
			if($ip === $ip) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns url (or null if not set) on which user should be redirected if has no access
	 *
	 * @return null|string
	 */
	public function redirect() {
		return $this->redirect;
	}

}
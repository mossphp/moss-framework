<?php
namespace Moss\security;

use \Moss\security\UserInterface;
use \Moss\http\request\RequestInterface;

/**
 * Secure area interface
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface AreaInterface {

	/**
	 * Returns area pattern
	 *
	 * @return string
	 */
	public function pattern();

	/**
	 * Returns array containing roles with access
	 *
	 * @return array
	 */
	public function roles();

	/**
	 * Returns array containing allowed IP addresses
	 *
	 * @return array
	 */
	public function ips();

	/**
	 * Returns true if area matches request
	 *
	 * @param RequestInterface $Request
	 *
	 * @return bool
	 */
	public function match(RequestInterface $Request);

	/**
	 * Returns true if use has access to area
	 *
	 * @param UserInterface $User
	 *
	 * @return bool
	 */
	public function authorizeUser(UserInterface $User);

	/**
	 * Returns true if IP may access area
	 *
	 * @param string $ip
	 *
	 * @return bool
	 */
	public function authorizeIP($ip);

	/**
	 * Returns url on which user should be redirected if has no access
	 *
	 * @return string
	 */
	public function redirect();
}
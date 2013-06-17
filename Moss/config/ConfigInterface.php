<?php
namespace Moss\config;

/**
 * Config interface
 *
 * @package Moss Config
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface ConfigInterface {

	/**
	 * Returns core variable value
	 * If variable is undefined - returns false
	 *
	 * @param string $var
	 *
	 * @return mixed
	 */
	public function get($var);
}
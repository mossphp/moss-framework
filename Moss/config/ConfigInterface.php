<?php
namespace moss\config;

/**
 * Config interface
 *
 * @package Moss Config
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface ConfigInterface {

	/**
	 * Reads configuration properties from passed array
	 *
	 * @param array $arr
	 */
	public function read($arr);

	/**
	 * Returns current stored configuration as array
	 *
	 * @return array
	 */
	public function save();

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
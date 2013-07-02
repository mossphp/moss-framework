<?php
namespace Moss\config;

use Moss\config\ConfigInterface;

/**
 * Configuration representation
 *
 * @package Moss Config
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Config implements ConfigInterface, \ArrayAccess {

	protected $error = array('level' => -1, 'detail' => true);
	protected $session = array('host' => true, 'ip' => true, 'agent' => true, 'salt' => null);
	protected $cookie = array();

	/**
	 * Creates Config instance
	 *
	 * @param array $cArr
	 */
	public function __construct($cArr = array()) {
		$this->error['level'] = $this->getArrValue($cArr, 'error.level', -1);
		$this->error['detail'] = $this->getArrValue($cArr, 'error.detail', true);

		$this->session['host'] = $this->getArrValue($cArr, 'session.host', true);
		$this->session['ip'] = $this->getArrValue($cArr, 'error.ip', true);
		$this->session['agent'] = $this->getArrValue($cArr, 'error.agent', true);
		$this->session['salt'] = $this->getArrValue($cArr, 'error.salt', null);
	}

	/**
	 * Returns offset value from array or default value if offset does not exists
	 *
	 * @param array|\ArrayAccess  $arr
	 * @param string              $offset
	 * @param mixed               $default
	 *
	 * @return mixed
	 */
	protected function getArrValue($arr, $offset, $default = null) {
		$keys = explode('.', $offset);
		while($i = array_shift($keys)) {
			if(!isset($arr[$i])) {
				return $default;
			}

			$arr = $arr[$i];
		}

		return $arr;
	}

	/**
	 * Returns core variable value
	 * If variable is undefined - returns false
	 *
	 * @param string $var name of core variable
	 *
	 * @return mixed
	 */
	public function get($var) {
		return $this->getArrValue($this, $var, null);
	}

	/**
	 * Offset to retrieve
	 *
	 * @param string $offset
	 *
	 * @return mixed
	 */
	public function &offsetGet($offset) {
		return $this->$offset;
	}

	/**
	 * Whether a offset exists
	 *
	 * @param string $offset
	 *
	 * @return bool
	 */
	public function offsetExists($offset) {
		return isset($this->$offset);
	}

	/**
	 * Sets value for offset
	 *
	 * @param int|string $offset offset to set
	 * @param mixed      $value  offsets value
	 *
	 * @throws \BadMethodCallException
	 */
	public function offsetSet($offset, $value) {
		throw new \BadMethodCallException('Forbidden! Read only');
	}

	/**
	 * Unsets offset
	 *
	 * @param int|string $offset offset to unset
	 *
	 * @throws \BadMethodCallException
	 */
	public function offsetUnset($offset) {
		throw new \BadMethodCallException('Forbidden! Read only');
	}
}
<?php
namespace Moss\config;

use \Moss\config\ConfigInterface;

/**
 * Configuration representation
 *
 * @package Moss Config
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Config implements ConfigInterface, \ArrayAccess {

	protected $kernel = array(
		'error' => array('level' => -1, 'detail' => true),
		'session' => array('host' => true, 'ip' => true, 'agent' => true, 'salt' => null),
		'cookie' => array()
	);

	protected $container = array();
	protected $dispatcher = array();
	protected $router = array();

	/**
	 * Creates Config instance
	 *
	 * @param array $cArr
	 */
	public function __construct($cArr = array()) {
		$this->kernel['error']['level'] = $this->getArrValue($cArr, 'kernel.error.level', -1);
		$this->kernel['error']['detail'] = $this->getArrValue($cArr, 'kernel.error.detail', true);

		$this->kernel['session']['host'] = $this->getArrValue($cArr, 'kernel.session.host', true);
		$this->kernel['session']['ip'] = $this->getArrValue($cArr, 'kernel.error.ip', true);
		$this->kernel['session']['agent'] = $this->getArrValue($cArr, 'kernel.error.agent', true);
		$this->kernel['session']['salt'] = $this->getArrValue($cArr, 'kernel.error.salt', null);

		$this->loaders = $this->getArrValue($cArr, 'loaders', array());
		$this->container = $this->getArrValue($cArr, 'container', array());
		$this->dispatcher = $this->getArrValue($cArr, 'dispatcher', array());
		$this->router = $this->getArrValue($cArr, 'router', array());
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
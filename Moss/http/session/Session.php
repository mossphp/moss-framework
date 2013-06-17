<?php
namespace Moss\http\session;

use \Moss\http\session\SessionInterface;

/**
 * Session object representation
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Session implements SessionInterface {
	protected $agent = true;
	protected $ip = true;
	protected $host = true;
	protected $salt;

	protected $storage;

	/**
	 * Creates session wrapper instance
	 * Also validates existing session - if session is invalid, resets it
	 *
	 * @param bool $agent
	 * @param bool $ip
	 * @param bool $host
	 * @param null $salt
	 */
	public function __construct($agent = true, $ip = true, $host = true, $salt = null) {
		$this->agent = (bool) $agent;
		$this->ip = (bool) $ip;
		$this->host = (bool) $host;
		$this->salt = (int) $salt;

		if(!session_id()) {
			session_start();
		}

		$this->storage = & $_SESSION;

		if(!$this->validate()) {
			$this->reset();
		}
	}

	/**
	 * Clears all session data
	 *
	 * @return Session
	 */
	public function reset() {
		unset($this->storage);

		$_SESSION = array();
		session_destroy();
		session_start();

		$this->storage = & $_SESSION;
		$this->storage['authkey'] = $this->authkey();

		return $this;
	}

	/**
	 * Validates session
	 *
	 * @return bool
	 */
	protected function validate() {
		return !empty($this->storage['authkey']) && $this->storage['authkey'] == $this->authkey();
	}

	/**
	 * Generates session auth key
	 *
	 * @return string
	 */
	protected function authkey() {
		$authkey = array();

		if($this->agent) {
			$authkey[] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'UndefinedUserAgent';
		}

		if($this->ip) {
			if(!empty($_SERVER['REMOTE_ADDR'])) {
				$authkey[] = $_SERVER['REMOTE_ADDR'];
			}
			elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$authkey[] = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			elseif(!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$authkey[] = $_SERVER['HTTP_CLIENT_IP'];
			}
			else {
				$authkey[] = 'UnknownIp';
			}
		}

		if($this->host) {
			$authkey[] = $_SERVER['SCRIPT_FILENAME'] . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'CGI');
		}

		return hash('sha512', implode($authkey) . $this->generateSalt($this->salt), false);
	}

	/**
	 * Generates salt
	 *
	 * @param int $length
	 *
	 * @return string
	 */
	protected function generateSalt($length) {
		$r = array();
		for($i = 0; $i < $length; ++$i) {
			$r[] = pack('S', mt_rand(0, 0xffff));
		}

		return substr(base64_encode(implode($r)), 0, 2);
	}

	/**
	 * Offset to unset
	 *
	 * @param string $offset
	 */
	public function offsetUnset($offset) {
		unset($this->storage[$offset]);
	}

	/**
	 * Offset to set
	 *
	 * @param string $offset
	 * @param mixed  $value
	 */
	public function offsetSet($offset, $value) {
		if(empty($offset)) {
			$offset = array_push($this->storage, $value);
		}

		$this->storage[$offset] = $value;
	}

	/**
	 * Offset to retrieve
	 *
	 * @param string $offset
	 *
	 * @return mixed
	 */
	public function &offsetGet($offset) {
		if(!isset($this->storage[$offset])) {
			$this->storage[$offset] = null;
		}

		return $this->storage[$offset];
	}

	/**
	 * Whether a offset exists
	 *
	 * @param string $offset
	 *
	 * @return bool
	 */
	public function offsetExists($offset) {
		return isset($this->storage[$offset]);
	}

	/**
	 * Return the current element
	 *
	 * @return mixed
	 */
	public function current() {
		return current($this->storage);
	}

	/**
	 * Move forward to next element
	 */
	public function next() {
		next($this->storage);
	}

	/**
	 * Return the key of the current element
	 *
	 * @return mixed
	 */
	public function key() {
		return key($this->storage);
	}

	/**
	 * Checks if current position is valid
	 *
	 * @return bool
	 */
	public function valid() {
		$key = key($this->storage);

		while($key !== null && $key !== 'authkey') {
			$this->next();
			$key = key($this->storage);
		}

		if($key === false || $key === null) {
			return false;
		}

		return isset($this->storage[$key]);
	}

	/**
	 * Rewind the Iterator to the first element
	 */
	public function rewind() {
		reset($this->storage);
	}

	/**
	 * Count elements of an object
	 *
	 * @return int
	 */
	public function count() {
		return count($this->storage) - 1;
	}
}
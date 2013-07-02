<?php
namespace Moss\http\session;

use Moss\http\session\FlashBagInterface;
use Moss\http\session\SessionInterface;

/**
 * Session flash bag
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class FlashBag implements FlashBagInterface {

	/** @var SessionInterface */
	private $Session;
	private $prefix;

	/**
	 * Constructor
	 * Binds flashbag with container array
	 *
	 * @param SessionInterface $Session
	 * @param string           $prefix
	 */
	public function __construct(SessionInterface $Session, $prefix = 'FlashBag') {
		$this->Session = & $Session;
		$this->prefix = $prefix;
		if(!isset($this->Session[$this->prefix])) {
			$this->Session[$this->prefix] = array();
		}
	}

	/**
	 * Removes all messages from container
	 */
	public function clear() {
		$this->Session[$this->prefix] = array();

		return $this;
	}

	/**
	 * Adds message to flashbag
	 *
	 * @param string $message
	 * @param string $type
	 *
	 * @return $this
	 */
	public function add($message, $type = 'error') {
		$this->Session[$this->prefix][] = array('message' => $message, 'type' => $type);

		return $this;
	}

	/**
	 * Returns true if at least one message of set type exists
	 *
	 * @param null|string $type
	 *
	 * @return bool
	 */
	public function has($type = null) {
		if(!$type) {
			return !empty($this->Session[$this->prefix]);
		}

		foreach($this->Session[$this->prefix] as $message) {
			if($message['type'] === $type) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns array containing messages of set type
	 *
	 * @param null|string $type
	 *
	 * @return array
	 */
	public function get($type = null) {
		$result = array();

		foreach($this->Session[$this->prefix] as $i => $message) {
			if($message['type'] !== $type) {
				$result[] = $message;
				unset($this->Session[$this->prefix][$i]);
			}
		}

		return $result;
	}

	/**
	 * Returns next message
	 *
	 * @return mixed
	 */
	public function retrieve() {
		return array_shift($this->Session[$this->prefix]);
	}

	/**
	 * Whether a offset exists
	 *
	 * @param mixed $offset
	 *
	 * @return boolean true on success or false on failure.
	 */
	public function offsetExists($offset) {
		return isset($this->Session[$this->prefix][$offset]);
	}

	/**
	 * Offset to retrieve
	 *
	 * @param mixed $offset
	 *
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		if(!isset($this->Session[$this->prefix][$offset])) {
			return null;
		}

		$result = $this->Session[$offset];
		unset($this->Session[$this->prefix][$offset]);

		return $result;
	}

	/**
	 * Offset to set
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		if($offset === null) {
			$offset = count($this->Session[$this->prefix]);
		}

		$this->Session[$this->prefix][$offset] = $value;
	}

	/**
	 * Offset to unset
	 *
	 * @param mixed $offset
	 *
	 * @return void
	 */
	public function offsetUnset($offset) {
		unset($this->Session[$this->prefix][$offset]);
	}

	/**
	 * Count elements of an object
	 *
	 * @return int
	 */
	public function count() {
		return count($this->Session[$this->prefix]);
	}


	/**
	 * Return the current element
	 *
	 * @return mixed
	 */
	public function current() {
		reset($this->Session[$this->prefix]);

		return array_shift($this->Session[$this->prefix]);
	}

	/**
	 * Return the key of the current element
	 *
	 * @return mixed
	 */
	public function key() {
		return key($this->Session[$this->prefix]);
	}

	/**
	 * Move forward to next element
	 *
	 * @return void
	 */
	public function next() {
		reset($this->Session[$this->prefix]);
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @return void
	 */
	public function rewind() {
		reset($this->Session[$this->prefix]);
	}

	/**
	 * Checks if current position is valid
	 *
	 * @return boolean
	 */
	public function valid() {
		return reset($this->Session[$this->prefix]);
	}
}
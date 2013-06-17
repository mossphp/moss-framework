<?php
namespace Moss\view;

use \Moss\view\ViewInterface,
	\Moss\config\ConfigInterface,
	\Moss\http\request\RequestInterface;

/**
 * Moss view
 * Uses Twig as template engine
 *
 * @package Moss View
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class View implements ViewInterface {

	protected $template;
	protected $vars = array();

	/** @var \Twig_Environment */
	protected $Twig;

	/**
	 * Creates View instance
	 *
	 * @param RequestInterface  $Request
	 * @param ConfigInterface   $Config
	 * @param \Twig_Environment $Twig
	 */
	public function __construct(RequestInterface $Request, ConfigInterface $Config, \Twig_Environment $Twig) {
		$this->Request = & $Request;
		$this->Config = & $Config;
		$this->Twig = & $Twig;
	}

	/**
	 * Assigns template to view
	 *
	 * @param string $template path to template (supports namespaces)
	 *
	 * @return View
	 */
	public function template($template) {
		$this->template = $template;

		return $this;
	}

	/**
	 * Sets variable to be used in template
	 *
	 * @param string|array $offset variable name, if array - its key will be used as variable names
	 * @param null|mixed   $value  variable value
	 *
	 * @return View
	 * @throws \InvalidArgumentException
	 */
	public function set($offset, $value = null) {
		if(is_array($offset)) {
			foreach($offset as $key => $val) {
				$this->set($key, $val);
				unset($val);
			}

			return $this;
		}

		$this->setIntoArray($this->vars, explode('.', $offset), $value);

		return $this;
	}

	/**
	 * Retrieves variable value
	 *
	 * @param string $offset variable name
	 *
	 * @return mixed
	 * @throws \OutOfRangeException
	 */
	public function get($offset) {
		return $this->getFromArray($this->vars, explode('.', $offset));
	}

	/**
	 * Sets array elements value
	 *
	 * @param array  $arr
	 * @param string $keys
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	protected function setIntoArray(&$arr, $keys, $value) {
		$k = array_shift($keys);

		if(is_scalar($arr)) {
			$arr = (array) $arr;
		}

		if(!isset($arr[$k])) {
			$arr[$k] = null;
		}

		if(empty($keys)) {
			return $arr[$k] = $value;
		}

		return $this->setIntoArray($arr[$k], $keys, $value);
	}

	/**
	 * Returns array element matching key
	 *
	 * @param array $arr
	 * @param array $keys
	 *
	 * @return mixed
	 */
	protected function getFromArray(&$arr, $keys) {
		$k = array_shift($keys);
		if(!isset($arr[$k])) {
			return null;
		}

		if(empty($keys)) {
			return $arr[$k];
		}

		return $this->getFromArray($arr[$k], $keys);
	}

	/**
	 * Renders view
	 *
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function render() {
		if(!$this->template) {
			throw new \InvalidArgumentException('Undefined view or view file does not exists: ' . $this->template . '!');
		}

		$this->vars['Request'] = & $this->Request;
		$this->vars['Config'] = & $this->Config;

		return $this->Twig->render($this->template, $this->vars);
	}

	/**
	 * Renders and returns view as string
	 *
	 * @return string
	 */
	public function __toString() {
		try {
			return $this->render();
		}
		catch(\InvalidArgumentException $e) {
			return sprintf('%s (%s line:%s)', $e->getMessage(), $e->getFile(), $e->getLine());
		}
	}

	/**
	 * Offset to unset
	 *
	 * @param string $offset
	 */
	public function offsetUnset($offset) {
		unset($this->vars[$offset]);
	}

	/**
	 * Offset to set
	 *
	 * @param string $offset
	 * @param mixed  $value
	 */
	public function offsetSet($offset, $value) {
		if(empty($offset)) {
			$offset = array_push($_COOKIE, $value);
		}

		$this->vars[$offset] = $value;
	}

	/**
	 * Offset to retrieve
	 *
	 * @param string $offset
	 *
	 * @return mixed
	 */
	public function &offsetGet($offset) {
		if(!isset($this->vars[$offset])) {
			$this->vars[$offset] = null;
		}

		return $this->vars[$offset];
	}

	/**
	 * Whether a offset exists
	 *
	 * @param string $offset
	 *
	 * @return bool
	 */
	public function offsetExists($offset) {
		return isset($this->vars[$offset]);
	}

	/**
	 * Return the current element
	 *
	 * @return mixed
	 */
	public function current() {
		return current($this->vars);
	}

	/**
	 * Move forward to next element
	 */
	public function next() {
		next($this->vars);
	}

	/**
	 * Return the key of the current element
	 *
	 * @return mixed
	 */
	public function key() {
		return key($this->vars);
	}

	/**
	 * Checks if current position is valid
	 *
	 * @return boolean
	 */
	public function valid() {
		$key = key($this->vars);

		while($key !== null) {
			$this->next();
			$key = key($this->vars);
		}

		if($key === false || $key === null) {
			return false;
		}

		return isset($this->vars[$key]);
	}

	/**
	 * Rewind the Iterator to the first element
	 */
	public function rewind() {
		reset($this->vars);
	}

	/**
	 * Count elements of an object
	 *
	 * @return int
	 */
	public function count() {
		return count($this->vars);
	}
}
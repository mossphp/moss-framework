<?php
namespace moss\container;

use moss\container\ComponentInterface;
use moss\container\ContainerInterface;
use moss\container\ContainerException;

/**
 * Dependency Injection Component definition
 *
 * @package Moss DI Container
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Component extends ComponentInterface {

	protected $class;
	protected $arguments;
	protected $methods;

	/**
	 * Constructor
	 *
	 * @param string $class
	 * @param array  $arguments
	 * @param array  $calls
	 */
	public function __construct($class, $arguments = array(), $calls = array()) {
		$this->class = (string) $class;

		if(!empty($arguments)) {
			$this->arguments = (array) $arguments;
		}

		if(!empty($calls)) {
			$this->methods = (array) $calls;
		}
	}

	/**
	 * Returns component instance
	 *
	 * @param ContainerInterface $Container
	 *
	 * @return object
	 */
	public function get(ContainerInterface $Container = null) {
		if(empty($this->arguments)) {
			$instance = new $this->class();
		}
		else {
			$Ref = new \ReflectionClass($this->class);
			$instance = $Ref->newInstanceArgs($this->prepare($Container, $this->arguments));
		}

		if(empty($this->methods)) {
			return $instance;
		}

		foreach($this->methods as $method => $methodArguments) {
			$ref = new \ReflectionMethod($instance, $method);

			if(empty($this->arguments)) {
				$ref->invoke($instance);
			}

			$ref->invokeArgs($instance, $this->prepare($Container, $methodArguments));
		}

		return $instance;
	}

	/**
	 * Retrieves needed arguments from container and returns them
	 *
	 * @param ContainerInterface $Container
	 * @param array              $arguments
	 *
	 * @return array
	 * @throws ContainerException
	 */
	protected function prepare(ContainerInterface $Container = null, $arguments = array()) {
		$result = array();

		foreach($arguments as $k => $arg) {
			if(is_array($arg)) {
				$result[$k] = $this->prepare($Container, $arg);
				continue;
			}

			if($arg == '@Container') {
				$result[$k] = & $Container;
				continue;
			}

			if(strpos($arg, '@') !== 0) {
				$result[$k] = $arg;
				continue;
			}

			$arg = substr($arg, 1);

			if(!$Container) {
				throw new ContainerException(sprintf('Unable to resolve dependency for "%s" - missing dependency "%s"', $this->class, $arg));
			}

			$result[$k] = $Container->get($arg);
		}

		return $result;
	}
}
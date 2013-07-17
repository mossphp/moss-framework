<?php
namespace Moss\loader;

/**
 * Moss auto load handlers
 * Supports standard SPL auto loading handlers
 *
 * @package Moss Autoloader
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Loader {

	protected $namespaces = array();
	protected $prefixes = array();

	/**
	 * Registers an array of namespaces
	 *
	 * @param array $namespaces array with namespace - path pairs
	 */
	public function addNamespaces(array $namespaces) {
		foreach($namespaces as $namespace => $locations) {
			$this->addNamespace($namespace, $locations);
		}
	}

	/**
	 * Registers a namespace
	 *
	 * @param string       $namespace
	 * @param array|string $paths
	 */
	public function addNamespace($namespace, $paths) {
		foreach((array) $paths as $path) {
			if(!isset($this->namespaces[(string) $namespace])) {
				$this->namespaces[(string) $namespace] = array();
			}

			$length = strlen($path);
			if($length == 0 || $path[$length - 1] != '/') {
				$path .= '/';
			}

			$this->namespaces[(string) $namespace][] = realpath($path);
		}
	}

	/**
	 * Registers loader handler.
	 *
	 * @param bool $prepend
	 */
	public function register($prepend = false) {
		spl_autoload_register(array($this, 'handler'), true, $prepend);
	}

	/**
	 * Unregisters loader handler
	 */
	public function unregisterHandle() {
		spl_autoload_unregister(array($this, 'handler'));
	}

	/**
	 * Handles autoload calls
	 *
	 * @param string $className
	 *
	 * @return bool
	 */
	public function handler($className) {
		if($file = $this->findFile($className)) {
			return require $file;
		}

		return false;
	}

	/**
	 * Finds file in defined namespaces and prefixes
	 *
	 * @param string $className
	 *
	 * @return bool|string
	 */
	protected function findFile($className) {
		foreach($this->namespaces as $namespace => $paths) {
			if(false !== $lastNsPos = strripos($className, '\\')) {
				if($namespace && $namespace . '\\' !== substr($className, 0, strlen($namespace . '\\'))) {
					continue;
				}

				$fileName = str_replace('\\', DIRECTORY_SEPARATOR, substr($className, 0, $lastNsPos)) . DIRECTORY_SEPARATOR . substr($className, $lastNsPos + 1) . '.php';

				foreach($paths as $path) {
					$file = ($path !== null ? $path . DIRECTORY_SEPARATOR : '') . $fileName;

					if(is_file($file)) {
						return $file;
					}
				}

				continue;
			}

			if($namespace !== '' && 0 !== strpos($className, $namespace)) {
				continue;
			}

			foreach($paths as $path) {
				$file = $path . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
				if(is_file($file)) {
					return $file;
				}
			}
		}

		return false;
	}
}

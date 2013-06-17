<?php

class Twig_Bridge_Extension_Resource extends Twig_Extension {

	protected $forceCopy;
	protected $public;
	protected $bundle;
	protected $resources = array();

	public function __construct($forceCopy = false, $public = './resource/{bundle}/', $bundle = '../src/{bundle}/resource/') {
		$this->forceCopy = (bool) $forceCopy;
		$this->public = $public;
		$this->bundle = $bundle;

		$path = substr($public, 0, strrpos(rtrim($public, '/'), '/'));
		if(!is_dir($path) && !mkdir($path, 0777, true)) {
			throw new Twig_Error_Runtime('Unable to create public resource directory');
		}
	}

	public function getTokenParsers() {
		return array(new Twig_Bridge_TokenParser_Resource());
	}

	public function getName() {
		return 'Resource';
	}

	public function build($resource) {
		$arr = $this->split($resource);

		$public = strtr($this->public, $arr);
		$bundle = strtr($this->bundle, $arr);

		if($this->forceCopy) {
			$this->buildCopy($public, $bundle);

			return $this->buildResourceName($public, $arr['{directory}'], $arr['{file}']);
		}

		try {
			$this->buildLink($public, $bundle);
		}
		catch(\BadFunctionCallException $e) {
			$this->buildCopy($public, $bundle);
		}

		$this->resources[] = $bundle;

		return $this->buildResourceName($public, $arr['{directory}'], $arr['{file}']);
	}

	protected function split($identifier) {
		preg_match_all('/^(?P<bundle>.+):(?P<file>.+)$/i', $identifier, $matches, PREG_SET_ORDER);
		if(empty($matches[0]['bundle']) || ($modPos = strpos($matches[0]['bundle'], ':')) === false) {
			return $identifier;
		}

		$arr = array(
			'{bundle}' => substr($matches[0]['bundle'], 0, $modPos),
			'{directory}' => substr($matches[0]['bundle'], $modPos + 1),
			'{file}' => $matches[0]['file']
		);

		return $arr;
	}

	protected function buildResourceName($path, $directory, $file) {
		return rtrim($path, '/') . '/' . ($directory ? $directory . '/' : null) . $file;
	}

	protected function buildCopy($public, $bundle) {
		$it = new \RecursiveDirectoryIterator($bundle);
		$files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::SELF_FIRST);

		$l = strlen($bundle);

		/** @var $file \SplFileInfo */
		foreach($files as $file) {
			$target = $public . str_replace('\\', '/', substr($file->getPathname(), $l));

			if($file->isDir()) {
				if(is_dir($target)) {
					continue;
				}

				if(!mkdir($target, 0777, true)) {
					throw new \Twig_Error_Runtime(sprintf('Unable to create directory for resource %s', $target));
				}
				continue;
			}

			if(is_file($target) && $file->getMTime() <= filemtime($target)) {
				continue;
			}

			if(!copy($file->getPathname(), $target)) {
				throw new \Twig_Error_Runtime('Unable to copy resource file ' . $file->getPathname());
			}
		}
	}

	protected function buildLink($public, $bundle) {
		if(file_exists($public)) {
			return;
		}

		if(!$bundle = realpath($bundle)) {
			throw new \Twig_Error_Runtime('Unable to resolve resource path to ' . $bundle);
		}

		if(!symlink($bundle, $public)) {
			throw new \Twig_Error_Runtime('Unable to create symlink for resource ' . $bundle.' to '. $public);
		}
	}
}
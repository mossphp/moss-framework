<?php
class Twig_Bridge_Loader_Bridge implements Twig_LoaderInterface {

	protected $moduleSeparator;
	protected $pattern;

	public function __construct($moduleSeparator = ':', $pattern = '../src/{bundle}/view/{directory}/{file}') {
		$this->moduleSeparator = $moduleSeparator;
		$this->pattern = $pattern;
	}

	public function getSource($name) {
		return file_get_contents($this->traslate($name));
	}

	public function getCacheKey($name) {
		return $this->traslate($name);
	}

	public function isFresh($name, $time) {
		$file = $this->traslate($name);
		return filemtime($file) < $time;
	}

	protected function traslate($name) {
		$quotedSeparator = preg_quote($this->moduleSeparator);
		preg_match_all('/^(?P<bundle>[^' . $quotedSeparator . ']+)' . $quotedSeparator . '(?P<directory>[^'.$quotedSeparator.']*'.$quotedSeparator.')?(?P<file>.+)$/', $name, $matches, \PREG_SET_ORDER);
		$r = array();

		foreach($matches[0] as $k => $v) {
			$r['{'.$k.'}'] = str_replace($this->moduleSeparator, '//', $v);
		}

		$file = strtr($this->pattern, $r);
		$file = str_replace(array('\\', '_', '//'), '/', $file);

		if(!is_file($file)) {
			throw new Twig_Error_Loader(sprintf('Unable to load template file %s (%s)', $name, $file));
		}

		return $file;
	}
}

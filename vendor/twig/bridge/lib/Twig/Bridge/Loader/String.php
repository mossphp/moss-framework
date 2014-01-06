<?php
class Twig_Bridge_Loader_String implements Twig_LoaderInterface {
	public function getSource($name) {
		return $name;
	}

	public function exists($name) {
		return true;
	}

	public function getCacheKey($name) {
		return $name;
	}

	public function isFresh($name, $time) {
		return true;
	}
}

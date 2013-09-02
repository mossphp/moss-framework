<?php
namespace tests\Moss;

class Foobar {
	public $args;

	public function __construct() {
		$this->args = func_get_args();
	}

	public function foo() {
		$this->args = func_get_args();
	}
}
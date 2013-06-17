<?php
namespace Moss\kernel;

/**
 * Moss error handler
 *
 * @package Moss Kernel
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class ErrorHandler {

	protected $level;

	/**
	 * Constructor
	 *
	 * @param int $level
	 */
	public function __construct($level = -1) {
		$this->level = (int) $level;
	}

	/**
	 * Registers handler and sets corresponding error reporting
	 */
	public function register() {
		set_error_handler(array($this, 'handler'), $this->level);
	}


	/**
	 * Unregisters handler
	 */
	public function unregister() {
		restore_error_handler();
	}

	/**
	 * Handles errors, throws them as Exceptions
	 *
	 * @param int    $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int    $errline
	 * @param null   $errcontext
	 *
	 * @throws \ErrorException
	 */
	public function handler($errno, $errstr, $errfile, $errline, $errcontext = null) {
		throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
	}
}
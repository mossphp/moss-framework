<?php
namespace Moss\Kernel;

/**
 * Moss error handler
 *
 * @package Moss Kernel
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class ErrorHandler
{

    protected $level;

    /**
     * Constructor
     *
     * @param boolean $display
     * @param int     $level
     */
    public function __construct($display = true, $level = -1)
    {
        $this->display = (bool) $display;
        $this->level = (int) $level;
    }

    /**
     * Registers handler and sets corresponding error reporting
     */
    public function register()
    {
        ini_set('display_errors', $this->display);

        error_reporting($this->level);
        set_error_handler(array($this, 'handler'), $this->level);
    }


    /**
     * Unregisters handler
     */
    public function unregister()
    {
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
    public function handler($errno, $errstr, $errfile, $errline, $errcontext = null)
    {
        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
}

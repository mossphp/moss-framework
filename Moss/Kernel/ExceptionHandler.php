<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Kernel;

/**
 * Moss exception handler
 *
 * @package Moss Kernel
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class ExceptionHandler
{

    private $details = false;
    private $depthLimit;

    /**
     * Constructor
     *
     * @param bool $verbose
     * @param int $depthLimit
     */
    public function __construct($verbose = false, $depthLimit = 8)
    {
        $this->verbose($verbose);
        $this->depthLimit($depthLimit);
    }

    /**
     * Sets reporting details
     *
     * @param bool $verbose
     *
     * @return bool
     */
    public function verbose($verbose = null)
    {
        if ($verbose !== null) {
            $this->details = (bool) $verbose;
        }

        return $this->details;
    }

    /**
     * Sets depth limit
     *
     * @param int $depthLimit
     *
     * @return int
     */
    public function depthLimit($depthLimit = null)
    {
        if($depthLimit !== null) {
            $this->depthLimit = (int) $depthLimit;
        }

        return $this->depthLimit;
    }

    /**
     * Registers handler
     */
    public function register()
    {
        set_exception_handler($this->details ? [$this, 'handlerVerbose'] : [$this, 'handlerTerse']);
    }


    /**
     * Unregisters handler
     */
    public function unregister()
    {
        restore_exception_handler();
    }

    /**
     * Terse exception handler
     * Sends only simplified exception message in plain text
     *
     * @param \Exception $exception
     */
    public function handlerTerse(\Exception $exception)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            header('Content-type: text/plain; charset=UTF-8');
        }
        echo sprintf('Bad Moss: %s ( %s at line: %u )', $exception->getMessage(), $exception->getFile(), $exception->getLine());
    }

    /**
     * Verbose exception handler
     * Sends HTML message with file where error occurred and stack trace
     *
     * @param \Exception $exception
     */
    public function handlerVerbose(\Exception $exception)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            header('Content-type: text/html; charset=UTF-8');
        }

        echo sprintf(
            '<!DOCTYPE html>
        <html>
        <head>
            <title>Bad Moss: %1$s - %2$s - %3$s:%4$u</title>
            <style>
                body, code { font: medium/1.5em monospace; }

                div:nth-child(4) { position: fixed; right: 0.5em; top: 0.5em; width: 100%; padding: 0.25em 1em; }
                div:nth-child(4) a { text-decoration: none; padding: 0.25em 0.75em; color: #444; background: #aef; border-radius: 1em; }

                table { width: auto; border-collapse: collapse; overflow: hidden; }

                td { vertical-align: top; }
                td:nth-child(1) { position: relative; width: 3em; padding: 0 0.5em 0 0; text-align: right; color: #999; border-right: 1px solid #999; z-index: 1; }
                td:nth-child(2) { position: relative; padding: 0 0 0 0.5em; z-index: 2; }

                td, td span { white-space: nowrap; }
                td span#mark { position: relative; font-weight: bold; color: #f00; }
                td span#mark:after { content: \'.\'; position: absolute; top: -0.2em; left: -2em; width: 10000em; background: #f00; opacity: 0.25; }
            </style>
        </head>
        <body>
            <h1>Bad Moss: %1$s - &quot;%2$s&quot;</h1>

                <div>
                    <h2 id="trace">Trace</h2>
                    %6$s
                </div>
                <div>
                    <h2 id="listing">File: %3$s:%4$u</h2>
                    %5$s
                </div>
                <div>
                    <a href="#trace">Trace</a>
                    <a href="#listing">Listing</a>
                    <a href="#mark">Error line</a>
                </div>
        </body>
        </html>',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $this->lineNumbers('<br />', highlight_file($exception->getFile(), true), $exception->getLine()),
            $this->prettyCode($exception->getTrace())
        );
    }

    /**
     * Adds line numbers
     *
     * @param string $lineSeparator
     * @param string $source
     * @param int   $mark
     *
     * @return string
     */
    public function lineNumbers($lineSeparator, $source, $mark = null)
    {
        $count = count(explode($lineSeparator, $source));
        $tpl = '<span %s>%u</span>';

        $lines = [];
        for ($i = 0; $i < $count; $i++) {
            $lines[] = sprintf($tpl, '', $i + 1);
        }

        $lines[$mark - 1] = sprintf($tpl, 'id="mark"', $mark);

        return sprintf('<table><tr><td>%s</td><td>%s</td></tr></table>', implode($lineSeparator, $lines), $source);
    }

    /**
     * Adds colors
     *
     * @param mixed $var
     *
     * @return string
     */
    public function prettyCode($var)
    {
        $var = $this->limit($var, 0);

        ob_start();


        if (extension_loaded('xdebug')) {
            var_dump($var);
        } else {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }

        return ob_get_clean();
    }

    /**
     * Limits dumped variable
     *
     * @param mixed $var
     * @param int   $depth
     * @param array $references
     *
     * @return string
     */
    public function limit($var, $depth, array &$references = [])
    {
        if ($depth > $this->depthLimit) {
            return '*DEPTH LIMIT*';
        }

        if (is_array($var)) {
            return $this->limitArray($var, $depth, $references);
        }

        if (is_object($var)) {
            return $this->limitObject($var, $depth, $references);
        }

        return $var;
    }

    /**
     * Limits dumped array
     *
     * @param mixed $var
     * @param int   $depth
     * @param array $references
     *
     * @return string
     */
    public function limitArray($var, $depth, array &$references)
    {
        foreach ($var as &$value) {
            $value = $this->limit($value, $depth + 1, $references);
            unset($value);
        }

        return $var;
    }

    /**
     * Limits dumped object
     *
     * @param mixed $var
     * @param int   $depth
     * @param array $references
     *
     * @return string
     */
    public function limitObject($var, $depth, array &$references)
    {
        $hash = spl_object_hash($var);

        if (in_array($hash, $references)) {
            return '*RECURSION*';
        }

        $references[] = $hash;
        $ref = new \ReflectionObject($var);
        foreach ($ref->getProperties() as $prop) {
            $prop->setAccessible(true);

            $prop->setValue($var, $this->limit($prop->getValue($var), $depth + 1, $references));
        }

        return $var;
    }
}

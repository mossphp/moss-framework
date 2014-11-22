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

    private $details;

    /**
     * Constructor
     *
     * @param bool $verbose
     */
    public function __construct($verbose = false)
    {
        $this->verbose($verbose);
    }

    /**
     * Sets reporting details
     *
     * @param bool $verbose
     *
     * @return $this
     */
    public function verbose($verbose = false)
    {
        $this->details = (bool) $verbose;

        return $this;
    }

    /**
     * Registers handler
     */
    public function register()
    {
        set_exception_handler($this->details ? array($this, 'handlerVerbose') : array($this, 'handlerTerse'));
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
        echo sprintf('Bad Moss: %s ( %s at line:%s )', $exception->getMessage(), $exception->getFile(), $exception->getLine());
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

                h1, h2 { font-size: 1.25em; }

                div:nth-child(4) { position: fixed; right: 0.5em; top: 0.5em; width: 100%; padding: 0.25em 1em; }
                div:nth-child(4) a { text-decoration: none; padding: 0.25em 0.75em; color: #444; background: #aef; border-radius: 1em; }

                table { width: auto; border-collapse: collapse; overflow: hidden; }

                td { vertical-align: top; }
                td:nth-child(1) { position: relative; width: 3em; padding: 0 0.5em 0 0; text-align: right; color: #999; border-right: 1px solid #999; z-index: 1; }
                td:nth-child(2) { position: relative; padding: 0 0 0 0.5em; z-index: 2; }

                td, td span { white-space: nowrap; }
                td span.mark { position: relative; font-weight: bold; color: #f00; }
                td span.mark:after { content: \'.\'; position: absolute; top: -0.2em; left: -2em; width: 10000em; background: #f00; opacity: 0.25; }
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
                    <a href="#error">Error line</a>
                </div>
        </body>
        </html>',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $this->lineNum('<br />', highlight_file($exception->getFile(), true), $exception->getLine()),
            $this->colorify($exception->getTrace())
        );
    }

    /**
     * Adds line numbers
     *
     * @param string $lineSeparator
     * @param string $source
     * @param null   $mark
     *
     * @return string
     */
    protected function lineNum($lineSeparator, $source, $mark = null)
    {
        $lines = array();
        $tpl = '<span %s>%u</span>';
        foreach (explode($lineSeparator, $source) as $i => $line) {
            if ($i + 1 == $mark) {
                $lines[] = sprintf($tpl, 'class="mark"', $i + 1);
                continue;
            }

            if ($i + 15 == $mark) {
                $lines[] = sprintf($tpl, 'id="error"', $i + 1);
                continue;
            }

            $lines[] = sprintf($tpl, '', $i + 1);
        }

        return sprintf('<table><tr><td>%s</td><td>%s</td></tr></table>', implode($lineSeparator, $lines), $source);
    }

    /**
     * Adds colors
     *
     * @param mixed $var
     *
     * @return mixed|string
     */
    public function colorify($var)
    {
        ob_start();
        var_dump($var);
        return ob_get_clean();
    }
}

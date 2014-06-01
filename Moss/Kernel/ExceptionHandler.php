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
    private $maxDepth;
    private $maxCount;
    private $maxStr;
    private $br = '<br />';
    private $indent = '&nbsp; &nbsp;';
    private $colors = array(
        'keyword' => '#007700',
        'object' => '#0000BB',
        'string' => '#DD0000',
        'numeric' => '#0000BB',
        'bool' => '#007700',
    );

    /**
     * Constructor
     *
     * @param bool $verbose
     * @param int  $maxDepth
     * @param int  $maxCount
     * @param int  $maxStr
     */
    public function __construct($verbose = false, $maxDepth = 10, $maxCount = 25, $maxStr = 128)
    {
        $this->verbose($verbose);
        $this->maxDepth($maxDepth);
        $this->maxCount($maxCount);
        $this->maxStr($maxStr);
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
     * Sets maximum depth of objects/arrays
     *
     * @param int $depth
     *
     * @return $this
     */
    public function maxDepth($depth = 10)
    {
        $this->maxDepth = (int) $depth;

        return $this;
    }

    /**
     * Sets maximum number of array elements
     *
     * @param int $count
     *
     * @return $this
     */
    public function maxCount($count = 25)
    {
        $this->maxCount = (int) $count;

        return $this;
    }

    /**
     * Sets maximum string length
     *
     * @param int $len
     *
     * @return $this
     */
    public function maxStr($len = 25)
    {
        $this->maxStr = (int) $len;

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
    public function handlerTerse($exception)
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
    public function handlerVerbose($exception)
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
                <h2>File: %3$s:%4$u</h2>
                %5$s
            </div>
            <div>
                <h2>Trace</h2>
                %6$s
            </div>
        </body>
        </html>',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $this->lineNum(highlight_file($exception->getFile(), true), $exception->getLine()),
            $this->lineNum($this->colorify($exception->getTrace()))
        );
    }

    /**
     * Adds line numbers
     *
     * @param      $source
     * @param null $mark
     *
     * @return string
     */
    protected function lineNum($source, $mark = null)
    {
        $lines = array();
        foreach (explode($this->br, $source) as $i => $line) {
            $lines[] = '<span ' . ($i + 1 == $mark ? 'class="mark"' : '') . '>' . ($i + 1) . '</span>';
        }

        return sprintf('<table><tr><td>%s</td><td>%s</td></tr></table>', implode($this->br, $lines), $source);
    }

    /**
     * Adds colors
     *
     * @param mixed $param
     * @param bool  $returnOnly
     * @param int   $indent
     * @param array $references
     *
     * @return mixed|string
     */
    protected function colorify($param, $returnOnly = false, $indent = 0, $references = array())
    {
        $str = '';

        if (is_null($param)) {
            $str = '<span style="color: ' . $this->colors['keyword'] . '">null</span>';
        } elseif (is_object($param)) {
            $hash = spl_object_hash($param);

            if (in_array($hash, $references, true)) {
                $str = '*RECURSION*';
            } else {
                $references[] = $hash;

                $className = get_class($param);
                $r = new \ReflectionClass($className);

                $str = '<span style="color: ' . $this->colors['object'] . '">' . $className . ' Object</span> (';

                if ($this->maxDepth && ($this->maxDepth <= $indent)) {
                    $str .= '*DEPTH LIMIT*)';
                } else {
                    foreach ($r->getConstants() as $eachConstantName => $eachConstant) {
                        $str .= $this->br . str_repeat($this->indent, $indent + 1) . '<span style="color: ' . $this->colors['keyword'] . '">const</span> [' . $eachConstantName . ']' . ' => ' . $this->colorify($eachConstant);
                    }

                    $staticPNames = array_keys($r->getStaticProperties());

                    $allPNames = array_map(
                        function ($property) {
                            return $property->name;
                        },
                        $r->getProperties()
                    );

                    $propertyNames = array_merge($staticPNames, array_diff($allPNames, $staticPNames));

                    foreach ($propertyNames as $eachPropertyName) {
                        $p = new \ReflectionProperty($className, $eachPropertyName);

                        $m = $p->getModifiers();
                        $visiblity = ($m & \ReflectionProperty::IS_PRIVATE ? 'private' : '') . ($m & \ReflectionProperty::IS_PROTECTED ? 'protected' : '') . ($m & \ReflectionProperty::IS_PUBLIC ? 'public' : '');
                        $isStatic = $m & \ReflectionProperty::IS_STATIC ? true : false;
                        $p->setAccessible(true);

                        $val = $isStatic ? $p->getValue() : $p->getValue($param);

                        $str .= $this->br . '<span style="color: ' . $this->colors['keyword'] . '">' . str_repeat($this->indent, $indent + 1) . ($isStatic ? 'static ' : '') . '[' . $eachPropertyName . ':' . $visiblity . ']</span>' . " => " . $this->colorify($val, true, $indent + 1, $references);
                    }
                    $str .= $this->br . str_repeat($this->indent, $indent) . ')';
                }

                array_pop($references);
            }
        } elseif (is_array($param)) {
            try {
                $hash = md5(serialize($param));
            } catch (\Exception $e) {
                $hash = null;
            }

            if (empty($param)) {
                $str .= 'Array()';
            } elseif (in_array($hash, $references, true)) {
                $str = '*RECURSION*';
            } else {
                $references[] = $hash;
                $str .= 'Array(' . count($param) . ') (';

                if ($this->maxDepth && ($this->maxDepth <= $indent)) {
                    $str .= '*DEPTH LIMIT*)';
                } else {
                    $c = 0;
                    foreach ($param as $eachKey => $eachValue) {
                        if ($this->maxCount && $c > $this->maxCount) {
                            $str .= $this->br . str_repeat($this->indent, $indent + 1) . '... (*COUNT LIMIT*)';
                            break;
                        }

                        $str .= $this->br . str_repeat($this->indent, $indent + 1) . '[' . $eachKey . "] => " . $this->colorify($eachValue, true, $indent + 1, $references);
                        $c++;
                    }
                    $str .= $this->br . str_repeat($this->indent, $indent) . ')';
                }
            }
        } elseif (is_string($param)) {
            $str = '<span style="color: ' . $this->colors['string'] . '">' . htmlspecialchars($this->maxStr && strlen($param) > $this->maxStr ? substr($param, 0, $this->maxStr) . '... (*STRING LIMIT*)' : $param) . '</span>';
        } elseif (is_numeric($param)) {
            $str = '<span style="color: ' . $this->colors['numeric'] . '">' . htmlspecialchars($param) . '</span>';
        } elseif (is_bool($param)) {
            $str = '<span style="color: ' . $this->colors['keyword'] . '">' . ($param ? 'true' : 'false') . '</span>';
        } else {
            $str = print_r($param, true);
        }

        if ($returnOnly) {
            return $str;
        }

        return $str;
    }
}

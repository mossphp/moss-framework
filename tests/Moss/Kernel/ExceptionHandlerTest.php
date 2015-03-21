<?php

/*
* This file is part of the moss-framework package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Moss\Kernel;

class FunctionMockExceptionHandler
{
    public static $handler_callback;
    public static $handler_restored = false;

    public static $xdebug_extension = false;
    public static $xdebug_var_dump = false;
}

function set_exception_handler($callback) { FunctionMockExceptionHandler::$handler_callback = $callback; }

function restore_exception_handler() { FunctionMockExceptionHandler::$handler_restored = true; }

function extension_loaded() { return FunctionMockExceptionHandler::$xdebug_extension; }

function var_dump($var) { echo $var; }

function headers_sent() { return false; }

function header($header) { echo $header . PHP_EOL; }

class ExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testVerbose()
    {
        $handler = new ExceptionHandler();
        $this->assertFalse($handler->verbose());

        $handler->verbose(true);
        $this->assertTrue($handler->verbose());
    }

    public function testRegisterTerse()
    {
        $handler = new ExceptionHandler(false);
        $handler->register();

        $this->assertEquals([$handler, 'handlerTerse'], FunctionMockExceptionHandler::$handler_callback);
    }

    public function testRegisterVerbose()
    {
        $handler = new ExceptionHandler(true);
        $handler->register();

        $this->assertEquals([$handler, 'handlerVerbose'], FunctionMockExceptionHandler::$handler_callback);
    }

    public function testUnregister()
    {
        $handler = new ExceptionHandler();
        $handler->register();
        $handler->unregister();

        $this->assertTrue(FunctionMockExceptionHandler::$handler_restored);
    }

    public function testTerseHandler()
    {
        $exception = new \Exception('Exception message');

        $handler = new ExceptionHandler();
        $handler->handlerTerse($exception);

        $expected = [
            'HTTP/1.1 500 Internal Server Error',
            'Content-type: text/plain; charset=UTF-8',
            'Bad Moss: Exception message ( ' . $exception->getFile() . ' at line: ' . $exception->getLine() . ' )'
        ];

        $this->expectOutputString(implode(PHP_EOL, $expected));
    }

    public function testVerboseHandler()
    {
        $exception = new \Exception('Exception message');

        $handler = new ExceptionHandler();
        $handler->handlerTerse($exception);

        $this->expectOutputRegex('/HTTP\/1.1 500 Internal Server Error/');
        $this->expectOutputRegex('/Content-type: text\/plain; charset=UTF-8/');
        $this->expectOutputRegex('/Bad Moss: Exception message \( ' . preg_quote($exception->getFile()) . ' at line: ' . $exception->getLine() . ' \)/');
    }

    public function testLineNumbers()
    {
        $handler = new ExceptionHandler();
        $result = $handler->lineNumbers(',', 'a,b,c,d', 3);

        $expected = '<table><tr><td><span >1</span>,<span >2</span>,<span id="mark">3</span>,<span >4</span></td><td>a,b,c,d</td></tr></table>';

        $this->assertEquals($expected, $result);
    }

    public function testPrettyCodeWithXdebug()
    {
        FunctionMockExceptionHandler::$xdebug_extension = true;

        $handler = new ExceptionHandler();
        $result = $handler->prettyCode('var');

        $this->assertEquals('var', $result);
    }

    public function testPrettyCodeWithoutXdebug()
    {
        FunctionMockExceptionHandler::$xdebug_extension = false;

        $handler = new ExceptionHandler();
        $result = $handler->prettyCode('var');

        $this->assertEquals('<pre>var</pre>', $result);
    }
}

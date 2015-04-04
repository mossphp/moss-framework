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
}

function set_exception_handler($callback) { FunctionMockExceptionHandler::$handler_callback = $callback; }

function restore_exception_handler() { FunctionMockExceptionHandler::$handler_restored = true; }

function extension_loaded() { return FunctionMockExceptionHandler::$xdebug_extension; }

class MockExceptionHandler extends ExceptionHandler
{
    public function prettyCode($var)
    {
        return 'code';
    }
}

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

        $handler = new MockExceptionHandler();
        $handler->handlerTerse($exception);

        $this->expectOutputString('Bad Moss: Exception message ( ' . $exception->getFile() . ' at line: ' . $exception->getLine() . ' )');
    }

    public function testVerboseHandler()
    {
        $exception = new \Exception('Exception message');

        $handler = new MockExceptionHandler();
        $handler->handlerVerbose($exception);

        $this->expectOutputRegex('/Bad Moss: Exception - Exception message - ' . preg_quote($exception->getFile(), '/') . ':' . $exception->getLine() . '/m');
    }

    public function testLineNumbers()
    {
        $handler = new ExceptionHandler();
        $result = $handler->lineNumbers(',', 'a,b,c,d', 3);

        $expected = '<table><tr><td><span >1</span>,<span >2</span>,<span id="mark">3</span>,<span >4</span></td><td>a,b,c,d</td></tr></table>';

        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider limitedVarsProvider
     */
    public function testPrettyCodeWithLimits($var, $expected)
    {
        FunctionMockExceptionHandler::$xdebug_extension = true;

        $handler = new ExceptionHandler();
        $handler->depthLimit(1);

        $result = $handler->limit($var, 0);

        $this->assertEquals($expected, $result);

    }

    public function limitedVarsProvider()
    {
        $object = new \stdClass();
        $object->foo = new \stdClass();
        $object->foo->bar = new \stdClass();

        $expectedObject = clone $object;
        $expectedObject->foo->bar = '*DEPTH LIMIT';

        $recursion = new \stdClass();
        $recursion->rec = $recursion;

        $expectedRecursion = clone $recursion;
        $expectedRecursion->rec = '*RECURSION*';

        return [
            [
                1,
                1
            ],
            [
                1.2,
                1.2
            ],
            [
                'foo bar yada',
                'foo bar yada'
            ],
            [
                ['foo' => ['bar' => ['yada' => 'daka daka']]],
                ['foo' => ['bar' => '*DEPTH LIMIT*']],
            ],
            [
                $object,
                $expectedObject
            ],
            [
                $recursion,
                $expectedRecursion
            ]
        ];

    }

    public function testPrettyCodeWithXdebug()
    {
        FunctionMockExceptionHandler::$xdebug_extension = true;

        $handler = new ExceptionHandler();
        $result = $handler->prettyCode('var');

        $this->assertEquals('string(3) "var"', str_replace(["\n", "\r"], '', $result));
    }

    public function testPrettyCodeWithoutXdebug()
    {
        FunctionMockExceptionHandler::$xdebug_extension = false;

        $handler = new ExceptionHandler();
        $result = $handler->prettyCode('var');

        $this->assertEquals('<pre>string(3) "var"</pre>', str_replace(["\n", "\r"], '', $result));
    }
}

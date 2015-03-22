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

class FunctionMockErrorHandler
{
    public static $level;
    public static $restored = false;
    public static $handler_callback;
    public static $handler_types;
}

function error_reporting($level) { FunctionMockErrorHandler::$level = $level; }

function set_error_handler($callback, $types)
{
    FunctionMockErrorHandler::$handler_callback = $callback;
    FunctionMockErrorHandler::$handler_types = $types;
}

function restore_error_handler() { FunctionMockErrorHandler::$restored = true; }

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider levelProvider
     */
    public function testRegister($level)
    {
        $handler = new ErrorHandler($level);
        $handler->register();

        $this->assertEquals($level, FunctionMockErrorHandler::$level);
        $this->assertEquals([$handler, 'handler'], FunctionMockErrorHandler::$handler_callback);
        $this->assertEquals($level, FunctionMockErrorHandler::$handler_types);
    }

    public function levelProvider()
    {
        return [
            [-1],
            [E_ERROR],
            [E_WARNING],
            [E_PARSE],
            [E_NOTICE],
            [E_CORE_ERROR],
            [E_CORE_WARNING],
            [E_COMPILE_ERROR],
            [E_COMPILE_WARNING],
            [E_USER_ERROR],
            [E_USER_WARNING],
            [E_USER_NOTICE],
            [E_STRICT],
            [E_RECOVERABLE_ERROR],
            [E_DEPRECATED],
            [E_USER_DEPRECATED],
            [E_ALL],
        ];
    }

    public function testUnregister()
    {
        $handler = new ErrorHandler(-1);
        $handler->register();
        $handler->unregister();

        $this->assertTrue(FunctionMockErrorHandler::$restored);
    }

    /**
     * @expectedException \ErrorException
     */
    public function testHandler()
    {
        $handler = new ErrorHandler(-1);
        $handler->handler(1, 'str', 'file', '123');
    }
}

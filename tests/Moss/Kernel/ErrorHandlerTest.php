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

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \ErrorException
     * @expectedExceptionMessage Manual error
     */
    public function testRegister()
    {
        $handler = new ErrorHandler(-1);
        $handler->register();

        @trigger_error('Manual error');
    }

    public function testUnregister()
    {
        $handler = new ErrorHandler(-1);
        $handler->register();
        $handler->unregister();

        @trigger_error('Manual error');
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

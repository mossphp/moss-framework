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


class AppTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Moss\Kernel\KernelException
     * @expectedExceptionMessage Missing required component
     */
    public function testMissingRequiredComponent()
    {
        $container = $this->getMock('\Moss\Container\ContainerInterface');
        $container->expects($this->once())
            ->method('exists');

        new App($container);
    }

    /**
     * @expectedException \Moss\Kernel\KernelException
     * @expectedExceptionMessage Invalid type for component
     */
    public function testInvalidTypeForRequiredComponent()
    {
        $container = $this->getMock('\Moss\Container\ContainerInterface');
        $container->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(true));

        $container->expects($this->once())
            ->method('get')
            ->will($this->returnValue(new \stdClass()));

        new App($container);
    }

    /**
     * @dataProvider componentProvider
     */
    public function testComponentsFromProperties($property, $expected)
    {
        $components = array(
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $this->getMock('\Moss\Http\Router\RouterInterface'),
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface'),
            'someComponent' => $this->getMock('\stdClass')
        );

        $container = $this->getMock('\Moss\Container\ContainerInterface');
        $container->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($name) use ($components) { return $components[$name]; }));

        $app = new App($container);

        $this->assertInstanceOf($expected, $app->{$property});
    }

    /**
     * @dataProvider componentProvider
     */
    public function testComponentsFromGetMethod($property, $expected)
    {
        $components = array(
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $this->getMock('\Moss\Http\Router\RouterInterface'),
            'dispatcher' => $this->getMock('\Moss\Dispatcher\DispatcherInterface'),
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface'),
            'someComponent' => $this->getMock('\stdClass')
        );

        $container = $this->getMock('\Moss\Container\ContainerInterface');
        $container->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($name) use ($components) { return $components[$name]; }));

        $app = new App($container);

        $this->assertInstanceOf($expected, $app->get($property));
    }

    public function componentProvider()
    {
        return array(
            array('config', '\Moss\Config\ConfigInterface'),
            array('router', '\Moss\Http\Router\RouterInterface'),
            array('dispatcher', '\Moss\Dispatcher\DispatcherInterface'),
            array('session', '\Moss\Http\Session\SessionInterface'),
            array('cookie', '\Moss\Http\Cookie\CookieInterface'),
            array('request', '\Moss\Http\Request\RequestInterface'),
            array('someComponent', '\stdClass'),
        );
    }

    public function testFireEvent()
    {
        $dispatcher = $this->getMock('\Moss\Dispatcher\DispatcherInterface');
        $dispatcher->expects($this->once())
            ->method('fire')
            ->with('some.event', null, null);

        $components = array(
            'config' => $this->getMock('\Moss\Config\ConfigInterface'),
            'router' => $this->getMock('\Moss\Http\Router\RouterInterface'),
            'dispatcher' => $dispatcher,
            'session' => $this->getMock('\Moss\Http\Session\SessionInterface'),
            'cookie' => $this->getMock('\Moss\Http\Cookie\CookieInterface'),
            'request' => $this->getMock('\Moss\Http\Request\RequestInterface'),
            'someComponent' => $this->getMock('\stdClass')
        );

        $container = $this->getMock('\Moss\Container\ContainerInterface');
        $container->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($name) use ($components) { return $components[$name]; }));

        $app = new App($container);
        $app->fire('some.event');
    }
}
 
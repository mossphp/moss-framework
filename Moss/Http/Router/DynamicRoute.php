<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Http\Router;

/**
 * Dynamic Route
 * Overloads Route and returns controller action from url as controller@action notation
 * Usage:
 *     $router->register(
 *          'dynamic',
 *          new DynamicRoute('/foo/{controller}/({action})', '\Some\Namespace\{controller}Controller@{action}Action')
 *     );
 * When calling /foo/Bar/yada
 *     \Some\Namespace\BarController@yadaAction will be called
 * If url does not provide action name, index will be used
 *
 * @package Moss Router
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class DynamicRoute extends Route
{
    /**
     * @var string
     */
    protected $controller;

    /**
     * Constructor
     *
     * @param string $pattern
     * @param string $controller
     * @param array  $arguments
     * @param array  $methods
     */
    public function __construct($pattern, $controller, array $arguments = [], array $methods = [])
    {
        parent::__construct($pattern, $controller, $arguments, $methods);
    }

    /**
     * Returns controller
     *
     * @return string
     */
    public function controller()
    {
        return strtr(
            $this->controller,
            [
                '{controller}' => str_replace('_', '\\', $this->arguments['controller']),
                '{action}' => $this->arguments['action'] ?: 'index'
            ]
        );
    }

    /**
     * Creates route url
     *
     * @param string $host
     * @param array  $arguments
     *
     * @return string
     */
    public function make($host, array $arguments = [])
    {
        if (isset($arguments['controller'])) {
            $arguments['controller'] = trim($arguments['controller'], '\\');
            $arguments['controller'] = preg_replace('/Controller$/', '', $arguments['controller']);
            $arguments['controller'] = str_replace('\\', '_', $arguments['controller']);
        }

        return parent::make($host, $arguments);
    }
}

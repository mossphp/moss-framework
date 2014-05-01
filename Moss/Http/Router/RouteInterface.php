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

use Moss\Http\Request\RequestInterface;

/**
 * Route definition interface for Router
 *
 * @package Moss Router
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface RouteInterface
{
    /**
     * Rebuilds pattern from regular expression
     *
     * @return string
     */
    public function pattern();

    /**
     * Returns controller
     *
     * @return string
     */
    public function controller();

    /**
     * Sets value requirements for each argument in pattern
     *
     * @param array $requirements
     *
     * @return $this
     */
    public function requirements($requirements = array());

    /**
     * Sets values for each argument in pattern
     *
     * @param array $arguments
     *
     * @return $this
     */
    public function arguments($arguments = array());

    /**
     * Sets host requirement
     *
     * @param null|string $host
     *
     * @return $this
     */
    public function host($host = null);

    /**
     * Sets allowed schema
     *
     * @param string $schema
     *
     * @return $this
     */
    public function schema($schema = null);

    /**
     * Sets allowed methods
     *
     * @param array $methods
     *
     * @return $this
     */
    public function methods($methods = array());

    /**
     * Returns controller if matches request, otherwise returns false
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function match(RequestInterface $request);

    /**
     * Check if arguments fit to
     *
     * @param       $controller
     * @param array $arguments
     *
     * @return bool
     */
    public function check($controller, $arguments = array());

    /**
     * Creates route url
     *
     * @param string $host
     * @param array  $arguments
     *
     * @return string
     */
    public function make($host, $arguments = array());
}

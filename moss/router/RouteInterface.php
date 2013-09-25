<?php
namespace moss\router;

use moss\http\request\RequestInterface;

/**
 * Route definition interface for Router
 *
 * @package Moss Router
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface RouteInterface
{

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
     * @param RequestInterface $Request
     *
     * @return bool
     */
    public function match(RequestInterface $Request);

    /**
     * Check if arguments fit to
     *
     * @param       $controller
     * @param array $arguments
     *
     * @return mixed
     */
    public function check($controller, $arguments = array());

    /**
     * Creates route url
     *
     * @param null|string $host
     * @param array       $arguments
     * @param bool        $forceRelative
     *
     * @return string
     */
    public function make($host = null, $arguments = array(), $forceRelative = true);
}
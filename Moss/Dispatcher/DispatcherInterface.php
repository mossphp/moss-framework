<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Dispatcher;

/**
 * Event dispatcher interface
 *
 * @package Moss Dispatcher
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface DispatcherInterface
{

    /**
     * Adds listener to single event or array of events
     *
     * @param string|array $event
     * @param callable     $listener
     * @param null|int     $priority
     *
     * @return $this
     */
    public function register($event, $listener, $priority = null);

    /**
     * Fires event
     *
     * @param string $event
     * @param mixed  $subject
     * @param string $message
     *
     * @return mixed
     * @throws \Exception
     */
    public function fire($event, $subject = null, $message = null);

    /**
     * Stops event handling
     *
     * @return $this
     */
    public function stop();
}

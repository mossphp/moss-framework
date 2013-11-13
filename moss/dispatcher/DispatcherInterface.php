<?php
namespace moss\dispatcher;

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
     * @param mixed  $message
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

<?php
namespace Moss\Dispatcher;

use Moss\Container\ContainerInterface;

/**
 * Event dispatcher
 *
 * @package Moss Dispatcher
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Dispatcher implements DispatcherInterface
{

    /** @var ContainerInterface */
    private $container;

    /** @var array */
    private $events = array();

    private $stop;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = & $container;
    }

    /**
     * Adds listener to single event or array of events
     *
     * @param string|array $event
     * @param callable     $listener
     * @param null|int     $priority
     *
     * @return $this
     */
    public function register($event, $listener, $priority = null)
    {
        foreach ((array) $event as $e) {
            $this->registerListener($e, $listener, $priority);
        }

        return $this;
    }

    /**
     * Register listener to event
     *
     * @param string                     $event
     * @param callable|ListenerInterface $listener
     * @param int                        $priority
     *
     * @throws DispatcherException
     */
    private function registerListener($event, $listener, $priority)
    {
        if (!is_callable($listener)) {
            throw new DispatcherException(sprintf('Invalid event listener. Only callables or ListenerInterface instances can be registered, got "%s"', gettype($listener)));
        }

        if (!isset($this->events[$event])) {
            $this->events[$event] = array();
        }

        if ($priority === null) {
            $this->events[$event][] = $listener;

            return;
        }

        array_splice($this->events[$event], (int) $priority, 0, array($listener));
    }

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
    public function fire($event, $subject = null, $message = null)
    {
        $this->stop = false;

        try {
            foreach (array($event . ':before', $event, $event . ':after') as $eventName) {
                if ($this->stop) {
                    break;
                }

                $subject = $this->call($eventName, $subject, $message);
            }

            return $subject;
        } catch (\Exception $e) {
            if (!isset($this->events[$event . ':exception'])) {
                throw $e;
            }

            return $this->call($event . ':exception', $e, $e->getMessage());
        }
    }

    /**
     * Stops event handling
     *
     * @return $this
     */
    public function stop()
    {
        $this->stop = true;

        return $this;
    }


    /**
     * Calls event listener
     *
     * @param string $eventName
     * @param mixed  $subject
     * @param mixed  $message
     *
     * @return mixed
     */
    protected function call($eventName, $subject = null, $message = null)
    {
        if (!isset($this->events[$eventName])) {
            return $subject;
        }

        foreach ($this->events[$eventName] as $listener) {
            if ($this->stop) {
                break;
            }

            $subject = $listener($this->container, $subject, $message, $eventName);
        }

        return $subject;
    }
}

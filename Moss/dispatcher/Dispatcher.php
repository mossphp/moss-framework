<?php
namespace Moss\dispatcher;

use Moss\dispatcher\DispatcherInterface;
use Moss\dispatcher\ListenerInterface;
use Moss\container\ContainerInterface;

/**
 * Event dispatcher
 *
 * @package Moss Dispatcher
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Dispatcher implements DispatcherInterface {

	/** @var ContainerInterface */
	private $Container;

	/** @var array */
	private $events = array();

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $Container
	 */
	public function __construct(ContainerInterface $Container = null) {
		$this->Container = & $Container;
	}

	/**
	 * Adds listener to single event or array of events
	 *
	 * @param string|array               $event
	 * @param \Closure|ListenerInterface $listener
	 * @param null|int                   $priority
	 *
	 * @return $this
	 */
	public function register($event, $listener, $priority = null) {
		if(is_array($event)) {
			foreach($event as $e) {
				$this->registerListener($e, $listener, $priority);
			}

			return $this;
		}

		$this->registerListener($event, $listener, $priority);

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
	private function registerListener($event, $listener, $priority) {
		if(!is_callable($listener) && !$listener instanceof ListenerInterface) {
			throw new DispatcherException(sprintf('Invalid event listener. Only callables or ListenerInterface instances can be registered, got %s', gettype($listener)));
		}

		if(!isset($this->events[$event])) {
			$this->events[$event] = array();
		}

		if($priority === null) {
			$this->events[$event][] = $listener;

			return;
		}

		array_splice($this->events[$event], (int) $priority, 0, array($listener));
	}

	/**
	 * Fires event
	 *
	 * @param string $event
	 * @param mixed  $Subject
	 * @param mixed  $message
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function fire($event, $Subject = null, $message = null) {
		try {
			foreach(array($event . ':before', $event, $event . ':after') as $eventName) {
				$Subject = $this->call($eventName, $Subject, $message);
			}
		}
		catch(\Exception $e) {
			if(!isset($this->events[$event . ':exception'])) {
				throw $e;
			}

			$Subject = $this->call($event . ':exception', $e, $e->getMessage());
		}

		return $Subject;
	}

	/**
	 * Calls event listener
	 *
	 * @param string $event
	 * @param mixed  $Subject
	 * @param mixed  $message
	 *
	 * @return mixed
	 */
	protected function call($event, $Subject = null, $message = null) {
		if(!isset($this->events[$event])) {
			return $Subject;
		}

		foreach($this->events[$event] as $listener) {
			if($listener instanceof ListenerInterface) {
				$Subject = $listener->get($this->Container, $Subject, $message);
				continue;
			}

			$Subject = $listener($this->Container, $Subject, $message);
		}

		return $Subject;
	}
}
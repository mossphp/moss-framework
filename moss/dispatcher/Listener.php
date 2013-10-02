<?php
namespace moss\dispatcher;

use moss\dispatcher\ListenerInterface;
use moss\container\ContainerInterface;
use moss\dispatcher\DispatcherException;

/**
 * Event dispatchers listener
 *
 * @package Moss Dispatcher
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Listener implements ListenerInterface
{

    protected $component;
    protected $arguments;
    protected $methods;

    /**
     * Constructor
     *
     * @param string $component
     * @param string $method
     * @param array  $arguments
     */
    public function __construct($component, $method = null, $arguments = array())
    {
        $this->component = (string) $component;
        $this->method = (string) $method;

        if (!empty($arguments)) {
            $this->arguments = (array) $arguments;
        }
    }

    /**
     * Returns component instance
     *
     * @param ContainerInterface $Container
     * @param mixed              $Subject
     * @param mixed              $message
     *
     * @return mixed
     */
    public function __invoke(ContainerInterface $Container, $Subject = null, $message = null) {
        return $this->get($Container, $Subject, $message);
    }

    /**
     * Returns component instance
     *
     * @param ContainerInterface $Container
     * @param mixed              $Subject
     * @param mixed              $message
     *
     * @return mixed
     */
    public function get(ContainerInterface $Container, $Subject = null, $message = null)
    {
        $instance = $Container->get($this->component);

        if (empty($this->method)) {
            return $instance;
        }

        $ref = new \ReflectionMethod($instance, $this->method);

        if (empty($this->arguments)) {
            $ref->invoke($instance);
            return $instance;
        }

        $ref->invokeArgs($instance, $this->prepare($Container, $this->arguments, $Subject, $message));

        return $instance;
    }

    /**
     * Retrieves needed arguments from container and returns them
     *
     * @param ContainerInterface $Container
     * @param array              $arguments
     * @param mixed              $Subject
     * @param mixed              $message
     *
     * @return array
     * @throws DispatcherException
     */
    protected function prepare(ContainerInterface $Container, $arguments = array(), $Subject = null, $message = null)
    {
        $result = array();

        foreach ($arguments as $k => $arg) {
            if (is_array($arg)) {
                $result[$k] = $this->prepare($Container, $arg);
                continue;
            }

            if ($arg == '@Container') {
                $result[$k] = $Container;
                continue;
            }

            if ($arg == '@Subject') {
                $result[$k] = $Subject;
                continue;
            }

            if ($arg == '@Message') {
                $result[$k] = $message;
                continue;
            }

            if (strpos($arg, '@') !== 0) {
                $result[$k] = $arg;
                continue;
            }

            $result[$k] = $Container->get(substr($arg, 1));
        }

        return $result;
    }
}
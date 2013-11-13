<?php
namespace moss\dispatcher;

use moss\container\ContainerInterface;

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
     * @param ContainerInterface $container
     * @param mixed              $subject
     * @param mixed              $message
     *
     * @return mixed
     */
    public function __invoke(ContainerInterface $container, $subject = null, $message = null)
    {
        return $this->get($container, $subject, $message);
    }

    /**
     * Returns component instance
     *
     * @param ContainerInterface $container
     * @param mixed              $subject
     * @param mixed              $message
     *
     * @return mixed
     */
    public function get(ContainerInterface $container, $subject = null, $message = null)
    {
        $instance = $container->get($this->component);

        if (empty($this->method)) {
            return $instance;
        }

        $ref = new \ReflectionMethod($instance, $this->method);

        if (empty($this->arguments)) {
            $ref->invoke($instance);

            return $instance;
        }

        $ref->invokeArgs($instance, $this->prepare($container, $this->arguments, $subject, $message));

        return $instance;
    }

    /**
     * Retrieves needed arguments from container and returns them
     *
     * @param ContainerInterface $container
     * @param array              $arguments
     * @param mixed              $subject
     * @param mixed              $message
     *
     * @return array
     * @throws DispatcherException
     */
    protected function prepare(ContainerInterface $container, $arguments = array(), $subject = null, $message = null)
    {
        $result = array();

        foreach ($arguments as $k => $arg) {
            if (is_array($arg)) {
                $result[$k] = $this->prepare($container, $arg);
                continue;
            }

            if ($arg == '@Container') {
                $result[$k] = $container;
                continue;
            }

            if ($arg == '@Subject') {
                $result[$k] = $subject;
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

            $result[$k] = $container->get(substr($arg, 1));
        }

        return $result;
    }
}

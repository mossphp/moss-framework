<?php
namespace moss\container;

/**
 * Dependency Injection Component definition
 *
 * @package Moss DI Container
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Component implements ComponentInterface
{

    protected $class;
    protected $arguments;
    protected $methods;

    /**
     * Constructor
     *
     * @param string $class
     * @param array  $arguments
     * @param array  $calls
     */
    public function __construct($class, $arguments = array(), $calls = array())
    {
        $this->class = (string) $class;

        if (!empty($arguments)) {
            $this->arguments = (array) $arguments;
        }

        if (!empty($calls)) {
            $this->methods = (array) $calls;
        }
    }

    /**
     * Returns component instance
     *
     * @param ContainerInterface $container
     *
     * @return object
     */
    public function __invoke(ContainerInterface $container = null)
    {
        return $this->get($container);
    }


    /**
     * Returns component instance
     *
     * @param ContainerInterface $container
     *
     * @return object
     */
    public function get(ContainerInterface $container = null)
    {
        if (empty($this->arguments)) {
            $instance = new $this->class();
        } else {
            $ref = new \ReflectionClass($this->class);
            $instance = $ref->newInstanceArgs($this->prepare($container, $this->arguments));
        }

        if (empty($this->methods)) {
            return $instance;
        }

        foreach ($this->methods as $method => $methodArguments) {
            $ref = new \ReflectionMethod($instance, $method);

            if (empty($this->arguments)) {
                $ref->invoke($instance);
            }

            $ref->invokeArgs($instance, $this->prepare($container, $methodArguments));
        }

        return $instance;
    }

    /**
     * Retrieves needed arguments from container and returns them
     *
     * @param ContainerInterface $container
     * @param array              $arguments
     *
     * @return array
     * @throws ContainerException
     */
    protected function prepare(ContainerInterface $container = null, $arguments = array())
    {
        $result = array();

        foreach ($arguments as $k => $arg) {
            if (is_array($arg)) {
                $result[$k] = $this->prepare($container, $arg);
                continue;
            }

            if ($arg == '@Container') {
                $result[$k] = & $container;
                continue;
            }

            if (strpos($arg, '@') !== 0) {
                $result[$k] = $arg;
                continue;
            }

            $arg = substr($arg, 1);

            if (!$container) {
                throw new ContainerException(sprintf('Unable to resolve dependency for "%s" - missing dependency "%s"', $this->class, $arg));
            }

            $result[$k] = $container->get($arg);
        }

        return $result;
    }
}

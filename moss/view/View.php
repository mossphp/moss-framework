<?php
namespace moss\view;

/**
 * Moss view
 * Uses Twig as template engine
 *
 * @package Moss View
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class View implements ViewInterface
{

    protected $template;
    protected $vars = array();

    /** @var \Twig_Environment */
    protected $twig;

    /**
     * Creates View instance
     *
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = & $twig;
    }

    /**
     * Assigns template to view
     *
     * @param string $template path to template (supports namespaces)
     *
     * @return View
     */
    public function template($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Sets variable to be used in template
     *
     * @param string|array $offset variable name, if array - its key will be used as variable names
     * @param null|mixed   $value  variable value
     *
     * @return View
     * @throws \InvalidArgumentException
     */
    public function set($offset, $value = null)
    {
        if (is_array($offset)) {
            foreach ($offset as $key => $val) {
                $this->set($key, $val);
                unset($val);
            }

            return $this;
        }

        $this->setIntoArray($this->vars, explode('.', $offset), $value);

        return $this;
    }

    /**
     * Retrieves variable value
     *
     * @param string $offset  variable name
     * @param mixed  $default default value if variable not found
     *
     * @return mixed
     */
    public function get($offset, $default = null)
    {
        return $this->getArrValue($this->vars, $offset, $default);
    }

    /**
     * Sets array elements value
     *
     * @param array  $array
     * @param string $keys
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function setIntoArray(&$array, $keys, $value)
    {
        $key = array_shift($keys);

        if (is_scalar($array)) {
            $array = (array) $array;
        }

        if (!isset($array[$key])) {
            $array[$key] = null;
        }

        if (empty($keys)) {
            return $array[$key] = $value;
        }

        return $this->setIntoArray($array[$key], $keys, $value);
    }

    /**
     * Returns offset value from array or default value if offset does not exists
     *
     * @param array|\ArrayAccess $array
     * @param string             $offset
     * @param mixed              $default
     *
     * @return mixed
     */
    protected function getArrValue($array, $offset, $default = null)
    {
        $keys = explode('.', $offset);
        while ($key = array_shift($keys)) {
            if (!isset($array[$key])) {
                return $default;
            }

            $array = $array[$key];
        }

        return $array;
    }

    /**
     * Renders view
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function render()
    {
        if (!$this->template) {
            throw new \InvalidArgumentException('Undefined view or view file does not exists: ' . $this->template . '!');
        }

        return $this->twig->render($this->template, $this->vars);
    }

    /**
     * Renders and returns view as string
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return (string) $this->render();
        } catch (\InvalidArgumentException $e) {
            return sprintf('%s (%s line:%s)', $e->getMessage(), $e->getFile(), $e->getLine());
        }
    }

    /**
     * Offset to unset
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->vars[$offset]);
    }

    /**
     * Offset to set
     *
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet($offset, $value)
    {
        if (empty($offset)) {
            $offset = array_push($_COOKIE, $value);
        }

        $this->vars[$offset] = $value;
    }

    /**
     * Offset to retrieve
     *
     * @param string $offset
     *
     * @return mixed
     */
    public function &offsetGet($offset)
    {
        if (!isset($this->vars[$offset])) {
            $this->vars[$offset] = null;
        }

        return $this->vars[$offset];
    }

    /**
     * Whether a offset exists
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->vars[$offset]);
    }

    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->vars);
    }

    /**
     * Move forward to next element
     */
    public function next()
    {
        next($this->vars);
    }

    /**
     * Return the key of the current element
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->vars);
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean
     */
    public function valid()
    {
        $key = key($this->vars);

        while ($key !== null) {
            $this->next();
            $key = key($this->vars);
        }

        if ($key === false || $key === null) {
            return false;
        }

        return isset($this->vars[$key]);
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind()
    {
        reset($this->vars);
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count()
    {
        return count($this->vars);
    }
}

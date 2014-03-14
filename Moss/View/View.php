<?php
namespace Moss\View;

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
    protected $vars;

    protected $pattern;

    /**
     * Creates View instance
     *
     * @param array  $vars
     * @param string $pattern
     */
    public function __construct(array $vars = array(), $pattern = '../src/{bundle}/{directory}/view/{file}.php')
    {
        $this->vars = $vars;
        $this->pattern = $pattern;
    }

    /**
     * Assigns template to view
     *
     * @param string $template path to template (supports namespaces)
     *
     * @return ViewInterface
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
        ob_start();
        extract($this->vars);
        require $this->traslate($this->template);

        return ob_get_clean();
    }

    protected function traslate($name)
    {
        preg_match_all('/^(?P<bundle>[^:]+):(?P<directory>[^:]*:)?(?P<file>.+)$/', $name, $matches, \PREG_SET_ORDER);

        $r = array();
        foreach (array('bundle', 'directory', 'file') as $k) {
            if (empty($matches[0][$k])) {
                throw new ViewException(sprintf('Invalid or missing "%s" node in view filename "%s"', $k, $name));
            }

            $r['{' . $k . '}'] = str_replace(':', '\\', $matches[0][$k]);
        }

        $file = strtr($this->pattern, $r);
        $file = str_replace(array('\\', '_', '//'), '/', $file);

        if (!is_file($file)) {
            throw new ViewException(sprintf('Unable to load template file %s (%s)', $name, $file));
        }

        return $file;
    }

    /**
     * Renders and returns view as string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
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

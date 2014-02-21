<?php
namespace Moss\loader;

/**
 * Moss auto load handlers
 * Supports standard SPL auto loading handlers
 *
 * @package Moss Autoloader
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Loader
{

    protected $namespaces = array();

    /**
     * Registers an array of namespaces
     *
     * @param array $namespaces array with namespace - path pairs
     *
     * @return $this
     */
    public function addNamespaces(array $namespaces)
    {
        foreach ($namespaces as $namespace => $locations) {
            $this->addNamespace($namespace, $locations);
        }

        return $this;
    }

    /**
     * Registers a namespace
     *
     * @param string       $namespace
     * @param array|string $paths
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addNamespace($namespace, $paths)
    {
        $namespace = rtrim($namespace, '\\');

        foreach ((array) $paths as $path) {
            if (!isset($this->namespaces[(string) $namespace])) {
                $this->namespaces[(string) $namespace] = array();
            }

            $length = strlen($path);
            if ($length == 0 || $path[$length - 1] != '/') {
                $path .= '/';
            }

            if (!is_dir($path)) {
                throw new \InvalidArgumentException(sprintf('Unable to resolve real path for "%s"', $path));
            }

            $this->namespaces[(string) $namespace][] = rtrim(realpath($path)) . DIRECTORY_SEPARATOR;
        }

        return $this;
    }

    /**
     * Registers loader handler.
     *
     * @param bool $prepend
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'handler'), true, $prepend);
    }

    /**
     * Unregisters loader handler
     */
    public function unregisterHandle()
    {
        spl_autoload_unregister(array($this, 'handler'));
    }

    /**
     * Handles autoload calls
     *
     * @param string $className
     *
     * @return bool
     */
    public function handler($className)
    {
        if ($file = $this->findFile($className)) {
            return require $file;
        }

        return false;
    }

    /**
     * Finds file in defined namespaces and prefixes
     *
     * @param string $className
     *
     * @return bool|string
     */
    public function findFile($className)
    {
        foreach ($this->namespaces as $namespace => $paths) {
            if (false !== $lastNsPos = strripos($className, '\\')) {
                $psr0 = str_replace('\\', '/', substr($className, 0, $lastNsPos)) . '/' . substr($className, $lastNsPos + 1) . '.php';
                $psr4 = ($namespace === '' ? $className : substr($className, strlen($namespace . '\\'))) . '.php';

                foreach ($paths as $path) {
                    $file = str_replace('\\', '/', $path . $psr0);
                    if ($this->requireFile($file)) {
                        return $file;
                    }

                    $file = str_replace('\\', '/', $path . $psr4);
                    if ($this->requireFile($file)) {
                        return $file;
                    }
                }

                continue;
            }

            foreach ($paths as $path) {
                $file = $path . str_replace('_', '/', $className) . '.php';

                if ($this->requireFile($file)) {
                    return $file;
                }
            }
        }

        return false;
    }

    /**
     * Requires file and returns boolean result
     *
     * @param string $file
     *
     * @return bool
     */
    protected function requireFile($file)
    {
        if (is_file($file)) {
            return $file;
        }

        return false;
    }
}

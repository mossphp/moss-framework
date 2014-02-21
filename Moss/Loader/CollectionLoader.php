<?php
namespace Moss\loader;

/**
 * Moss auto load handlers
 * Supports standard SPL auto loading handlers
 *
 * @package Moss Collection loader
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class CollectionLoader
{
    protected $cache;
    protected $preserveComments;

    protected $paths;

    protected $declared;

    protected $classes = array();
    protected $edges = array();

    /**
     * Constructor
     *
     * @param string $cache
     * @param bool   $preserveComments
     */
    public function __construct($cache, $preserveComments = false)
    {
        $this->cache = (string) $cache;
        $this->preserveComments = (bool) $preserveComments;

        $this->declared = array_merge(get_declared_classes(), get_declared_interfaces());
    }

    /**
     * Adds path (or paths if array passed) to gather classes from
     *
     * @param string|array $path
     */
    public function addPath($path)
    {
        if (!is_array($path)) {
            $this->addPathToList($path);

            return;
        }

        foreach ($path as $node) {
            $this->addPathToList($node);
        }
    }

    /**
     * Adds single path entry to loader
     *
     * @param string $path
     */
    protected function addPathToList($path)
    {
        $length = strlen($path);
        if ($length == 0 || $path[$length - 1] != '/') {
            $path .= '/';
        }

        $this->paths[$path] = realpath($path);
    }

    /**
     * Loads pre generated cache file
     * If cache does not exists, gathers classes and builds it
     */
    public function load()
    {
        if (is_file($this->cache)) {
            require $this->cache;

            return;
        }

        $this->gather();
    }

    public function gather()
    {
        $this->classes = array();
        $this->edges = array();

        foreach ($this->paths as $path) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

            foreach ($iterator as $item) {
                if (!$class = $this->identify($item)) {
                    continue;
                }

                if ($this->isDeclared($class)) {
                    continue;
                }

                $ref = new \ReflectionClass($class);

                $this->gatherParent($ref);
                $this->gatherInterface($ref);
                $this->gatherUses($ref);
            }
        }

        $this->classes = $this->sortByDependency();
        $this->classes = $this->sortByNamespaces();

        $content = null;
        foreach ($this->classes as $namespace => $classes) {

            $nsContent = '';
            $nsUses = array();
            foreach ($classes as $class) {
                $ref = new \ReflectionClass($class);

                $c = file_get_contents($ref->getFileName());

                if (!$this->preserveComments) {
                    $c = $this->stripComments($c);
                }

                $c = $this->fixUseDeclarations($c, $namespace, $nsUses);

                $c = $this->fixNamespaceDeclarations($c);
                $c = preg_replace(array('/^\s*<\?php/', '/\?>\s*$/'), '', $c);
                $c = trim($c);

                $nsContent .= "\n" . $c . "\n";
            }

            $content .= sprintf(
                "\nnamespace %s {\n\n%s\n%s\n}\n\n",
                $namespace !== '.' ? $namespace : null,
                !empty($nsUses) ? 'use ' . implode(",\n\t", array_unique($nsUses)) . ';' : null,
                $nsContent
            );
        }

        $content = "<?php\n" . $content;
        file_put_contents($this->cache, $content);
    }

    /**
     * Returns true if class was declared before
     *
     * @param string $class
     *
     * @return bool
     */
    protected function isDeclared($class)
    {
        return in_array($class, $this->declared);
    }

    /**
     * Identifies class in file
     * Returns class or interface name or false if no definition found or file is not valid
     *
     * @param \SplFileInfo $file
     *
     * @return bool|string
     */
    protected function identify(\SplFileInfo $file)
    {
        if (!$file->isFile()) {
            return false;
        }

        if (!preg_match('/^.*\.php$/', (string) $file)) {
            return false;
        }

        $content = file_get_contents($file->getPathname(), null, null, 0, 1024);

        preg_match_all('/^namespace (.+);/im', $content, $nsMatches);
        preg_match_all('/^(abstract )?(class) ([^ \n{]+).*$/im', $content, $nameMatches);

        if (!isset($nameMatches[3][0]) || empty($nameMatches[3][0])) {
            return false;
        }

        if (empty($nsMatches[1][0])) {
            return trim($nameMatches[3][0]);
        }

        return trim($nsMatches[1][0] . '\\' . $nameMatches[3][0]);
    }

    /**
     * Gathers parent classes recursively
     *
     * @param \ReflectionClass $ref
     */
    protected function gatherParent(\ReflectionClass $ref)
    {
        while ($parent = $ref->getParentClass()) {
            $this->addDependency($ref, $parent);
            $this->addDependency($parent);
            $ref = $parent;
        }
    }

    /**
     * Gathers implemented interfaces recursively
     *
     * @param \ReflectionClass $ref
     */
    protected function gatherInterface(\ReflectionClass $ref)
    {
        foreach ($ref->getInterfaces() as $interface) {
            $this->addDependency($ref, $interface);
            $this->addDependency($interface);
            $this->gatherParent($interface);
        }
    }

    /**
     * Gathers use definitions and adds them to class dependencies
     *
     * @param \ReflectionClass $ref
     */
    protected function gatherUses(\ReflectionClass $ref)
    {
        $source = file_get_contents($ref->getFileName());
        preg_match_all('/^use[ \n]*([^;]+);/im', $source, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return;
        }

        foreach ($matches as $match) {
            $match[1] = explode(',', str_replace(array("\n", "\r"), null, $match[1]));

            foreach ($match[1] as $class) {
                $this->addDependency($ref, new \ReflectionClass(trim($class, '\\')));
            }
        }
    }

    /**
     * Adds dependency to class list and edge list
     *
     * @param \ReflectionClass $ref
     * @param \ReflectionClass $dependency
     */
    protected function addDependency(\ReflectionClass $ref, \ReflectionClass $dependency = null)
    {
        if (!$ref->isUserDefined() || $this->isDeclared($ref->getName())) {
            return;
        }

        if (!in_array($ref->getName(), $this->classes)) {
            $this->classes[] = $ref->getName();
        }

        if (!$dependency || !$dependency->isUserDefined() || $this->isDeclared($dependency->getName())) {
            return;
        }

        if (!in_array($ref->getName(), $this->classes)) {
            $this->classes[] = $dependency->getName();
        }

        $this->edges[] = array($dependency->getName(), $ref->getName());
    }

    /**
     * Sort by dependency (topological sort)
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function sortByDependency()
    {
        $l = $s = $nodes = array();

        foreach ($this->classes as $id) {
            $nodes[$id] = array('in' => array(), 'out' => array());
            foreach ($this->edges as $e) {
                if ($id == $e[0]) {
                    $nodes[$id]['out'][] = $e[1];
                }
                if ($id == $e[1]) {
                    $nodes[$id]['in'][] = $e[0];
                }
            }
        }

        foreach ($nodes as $id => $n) {
            if (empty($n['in'])) {
                $s[] = $id;
            }
        }

        while ($id = array_shift($s)) {
            if (!in_array($id, $l)) {
                $l[] = $id;
                foreach ($nodes[$id]['out'] as $m) {
                    $nodes[$m]['in'] = array_diff($nodes[$m]['in'], array($id));
                    if (empty($nodes[$m]['in'])) {
                        $s[] = $m;
                    }
                }
                $nodes[$id]['out'] = array();
            }
        }

        foreach ($nodes as $n) {
            if (!empty($n['in']) || !empty($n['out'])) {
                throw new \InvalidArgumentException('Unable to sort as graph is cyclic');
            }
        }

        return $l;
    }

    /**
     * Builds array containing namespaces as keys and classes as elements
     *
     * @return array
     */
    protected function sortByNamespaces()
    {
        $classes = array();

        foreach ($this->classes as $class) {
            $namespace = $this->getClassNamespace($class);
            if (!isset($classes[$namespace])) {
                $classes[$namespace] = array();
            }

            $classes[$namespace][] = $class;
        }

        return $classes;
    }

    /**
     * Returns class namespace;
     *
     * @param string $class
     *
     * @return string
     */
    protected function getClassNamespace($class)
    {
        return substr($class, 0, strrpos($class, '\\'));
    }

    /**
     * Removes comments from class definition
     *
     * @param string $source
     *
     * @return string
     */
    protected static function stripComments($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (!in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= $token[1];
            }
        }

        $output = preg_replace(array('/\s+$/Sm', '/\n+/S'), "\n", $output);

        return $output;
    }

    /**
     * Removes namespace declaration from class
     *
     * @param string $source
     *
     * @return string
     */
    protected function fixNamespaceDeclarations($source)
    {
        $source = preg_replace('/^namespace [^;]+;/im', null, $source);

        return $source;
    }

    /**
     * Fixes use declarations in class definition
     *
     * @param string $source
     * @param string $namespace
     * @param array  $used
     *
     * @return string
     */
    protected function fixUseDeclarations($source, $namespace, &$used)
    {
        preg_match_all('/^use[ \n]*([^;]+);/im', $source, $matches, PREG_SET_ORDER);

        if (!isset($matches[0][0])) {
            return $source;
        }

        foreach ($matches as $match) {
            $match[1] = explode(',', str_replace(array("\n", "\r"), null, $match[1]));
            foreach ($match[1] as $node) {
                $node = ltrim($node, '\\');

                if (ltrim(dirname($node), '\\') == $namespace || in_array($node, $used)) {
                    continue;
                }

                $used[] = $node;
            }

            $source = str_replace($match[0], null, $source);
        }

        return $source;
    }
}

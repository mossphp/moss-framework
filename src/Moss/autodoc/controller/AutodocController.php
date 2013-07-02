<?php
namespace Moss\autodoc\controller;

use Moss\container\ContainerInterface;
use Moss\http\response\Response;

/**
 * Generates documentation based on PHPDoc comments
 *
 * @package AutoDoc
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class AutodocController {

	protected $Container;

	protected $files = array();
	protected $doc = array();
	protected $packages = array();
	protected $directories = array(
		'../Moss/'
	);
	protected $ignored = array();

	/**
	 * Constructor, calls init function
	 *
	 * @param ContainerInterface $Container
	 */
	public function __construct(ContainerInterface $Container) {
		$this->Container = & $Container;
	}

	/**
	 * Initializes Autodoc
	 * Prepares View and regular expression for ignored directories
	 */
	public function init() {
		if(!empty($this->ignored)) {
			$disabled = $this->ignored;

			$this->ignored = '(';
			foreach($disabled as $dir) {
				if(!empty($dir)) {
					$dir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $dir);
					$this->ignored .= '' . preg_quote($dir) . '|';
				}
			}

			$this->ignored[strlen($this->directories) - 1] = ')';
			$this->ignored = '#^' . $this->ignored . '.*$#';
		}
	}

	/**
	 * Creates autodoc
	 *
	 * @return Response
	 */
	public function index() {
		$this->gather();

		foreach($this->files as $file => $name) {
			include_once($file);
			$this->doc[$name] = $this->buildClassDoc(new \ReflectionClass($name));
		}

		$this->repairParameterTypes();
		$this->buildPackages();

		usort($this->doc, array($this, 'usort'));

		$autodocResponseContent = $this->Container
			->get('View')
			->template('Moss:autodoc:autodoc')
			->set('Doc', $this->doc)
			->set('Packages', $this->packages)
			->render();

		$autodocResponse = new Response($autodocResponseContent);

		return $autodocResponse;
	}

	protected function usort($a, $b) {
		if($a['namespace'] != $b['namespace']) {
			return $a['namespace'] > $b['namespace'] ? 1 : -1;
		}

		$result = $b['isInterface'] - $a['isInterface'];
		if($result) {
			return $result;
		}

		if($a['name'] == $b['name']) {
			return 0;
		}

		return $a['name'] > $b['name'] ? 1 : -1;
	}

	/**
	 * Gathers files from directories
	 *
	 * @return void
	 */
	protected function gather() {
		foreach($this->directories as $dir) {
			$RecursiveIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));

			foreach($RecursiveIterator as $item) {
				if(!$this->isValid($item)) {
					continue;
				}

				if(!$name = $this->identify((string) $item)) {
					continue;
				}

				$this->files[(string) $item] = $name;
			}
		}
	}

	/**
	 * Checks if file is valid
	 * Valid file has .php extension and is not in ignored directories
	 *
	 * @param \SplFileInfo $file
	 *
	 * @return bool
	 */
	protected function isValid(\SplFileInfo $file) {
		if(!$file->isFile()) {
			return false;
		}

		if(!preg_match('/^.*\.php$/', (string) $file)) {
			return false;
		}

		if($this->ignored && preg_match($this->ignored, (string) $file)) {
			return false;
		}

		return true;
	}

	/**
	 * Identifies namespace and interface/class declaration in file
	 *
	 * @param $file
	 *
	 * @return bool|null|string
	 */
	protected function identify($file) {
		$content = file_get_contents($file, null, null, 0, 1024);

		preg_match_all('/^namespace (.+);/im', $content, $nsMatches);
		preg_match_all('/^(abstract )?(interface|class) ([^ ]+).*$/im', $content, $nameMatches);

		if(!empty($nameMatches[3][0])) {
			return empty($nsMatches[1][0]) ? null : '\\' . $nsMatches[1][0] . '\\' . $nameMatches[3][0];
		}

		return false;
	}

	protected function buildClassDoc(\ReflectionClass $RefClass) {
		$doc = array(
			'desc' => $this->parseCommentDesc($RefClass->getDocComment(), true),
			'author' => $this->parseCommentDesc($RefClass->getDocComment(), 'author'),
			'package' => $this->parseCommentDesc($RefClass->getDocComment(), 'package'),
			'name' => '\\' . $RefClass->getNamespaceName() . '\\' . basename($RefClass->getName()),
			'namespace' => '\\' . $RefClass->getNamespaceName() . '\\',
			'parent' => $RefClass->getParentClass() ? '\\' . $RefClass
					->getParentClass()
					->getName() : null,
			'interfaces' => $RefClass->getInterfaceNames(),
			'properties' => array(),
			'methods' => array(),
			'isAbstract' => $RefClass->isAbstract(),
			'isInterface' => $RefClass->isInterface()
		);

		foreach($doc['interfaces'] as &$interface) {
			$interface = '\\' . $interface;
			unset($interface);
		}

		/** @var \ReflectionProperty $property */
		foreach($RefClass->getProperties() as $property) {
			if(!$property->isPublic()) {
				continue;
			}

			$doc['properties'][] = $property;
		}

		/** @var \ReflectionMethod $method */
		foreach($RefClass->getMethods() as $method) {
			$doc['methods'][] = $this->buildMethodDoc($method);
		}

		return $doc;
	}

	protected function buildMethodDoc(\ReflectionMethod $RefMethod) {
		$doc = array(
			'desc' => $this->parseCommentDesc($RefMethod->getDocComment(), true),
			'doc' => $this->parseCommentParameters($RefMethod->getDocComment()),
			'name' => $RefMethod->getName(),
			'isAbstract' => $RefMethod->isAbstract(),
			'isStatic' => $RefMethod->isStatic(),
			'isPublic' => $RefMethod->isPublic(),
			'isProtected' => $RefMethod->isProtected(),
			'isPrivate' => $RefMethod->isPrivate(),
			'isUserDefined' => $RefMethod->isUserDefined(),
			'arguments' => array()
		);

		foreach($RefMethod->getParameters() as $parameter) {
			$doc['arguments']['$' . $parameter->getName()] = $this->buildParameterDoc($parameter, $doc['doc']);
		}

		return $doc;
	}

	protected function buildParameterDoc(\ReflectionParameter $RefParameter, $comment) {
		$var = '$' . $RefParameter->getName();

		$doc = array(
			'name' => $RefParameter->getName(),
			'type' => isset($comment['param'][$var]) ? $comment['param'][$var]['type'] : null,
			'default' => $RefParameter->isDefaultValueAvailable() ? $RefParameter->getDefaultValue() : null,
			'required' => !$RefParameter->isOptional(),
			'desc' => isset($comment['param'][$var]) ? $comment['param'][$var]['desc'] : null,
		);

		return $doc;
	}

	protected function parseComment($comment) {
		$comment = preg_replace('#[ \t]*(?:\/\*\*|\*\/|\*)?[ ]{0,1}(.*)?#', '$1', $comment);
		$comment = str_replace(array("\t", "\r", "\n"), array(null, null, ' '), $comment);
		$comment = str_replace('  ', ' ', $comment);
		$comment = trim($comment);

		return $comment;
	}

	protected function parseCommentDesc($comment, $stripParams = true) {
		$comment = $this->parseComment($comment);

		if($stripParams === 'author') {
			if(stripos($comment, '@author') !== false) {
				return trim(preg_replace('/^.*@author([^>]+>?).*$/', '$1', $comment));
			}
			else {
				return null;
			}
		}
		elseif($stripParams === 'package') {
			if(stripos($comment, '@package') !== false) {
				return trim(preg_replace('/^.*@package([^@]+).*$/', '$1', $comment));
			}
			else {
				return null;
			}
		}
		elseif($stripParams) {
			return trim(preg_replace('/^([^@]*).*/i', '$1', $comment));
		}

		return $comment;
	}

	protected function repairParameterTypes() {
		$namespaces = array_keys($this->doc);
		foreach($namespaces as &$node) {
			$node = substr($node, 0, strrpos($node, '\\') + 1);
			unset($node);
		}
		$namespaces = array_unique($namespaces);

		foreach($this->doc as &$class) {
			foreach($class['methods'] as &$method) {
				foreach($method['arguments'] as &$argument) {
					$argument['type'] = $this->repairParameterNamespace($argument['type'], $class['namespace'], $namespaces);
					unset($argument);
				}
				unset($method);
			}
			unset($class);
		}
	}

	protected function repairParameterNamespace($type, $namespace, $namespaces) {
		if($type === null) {
			return null;
		}

		if(is_array($type)) {
			foreach($type as &$node) {
				$node = $this->repairParameterNamespace($node, $namespace, $namespaces);
				unset($node);
			}

			return $type;
		}

		if(strpos($type, '\\') === 0 || preg_match('/^(null|mixed|bool|boolean|int|integer|float|double|string|array|closure|object).*$/i', $type)) {
			return $type;
		}

		if(isset($this->doc[$namespace . $type])) {
			return $namespace . $type;
		}

		foreach($namespaces as $namespace) {
			if(isset($this->doc[$namespace . $type])) {
				return $namespace . $type;
			}
		}

		return '\\' . $type;
	}

	protected function parseCommentParameters($comment) {
		$doc = array();
		$comment = $this->parseComment($comment);

		preg_match_all('/@[^@]+/i', $comment, $matches);
		foreach($matches[0] as $def) {
			preg_match_all('/^@(?P<param>[^ ]+) *(?P<type>[^ ]+)? *(?P<var>\$[^ ]+)? *(?P<desc>.*)$/', trim($def), $nodes, PREG_SET_ORDER);

			if(!isset($nodes[0])) {
				continue;
			}

			if(!isset($doc[$nodes[0]['param']])) {
				$doc[$nodes[0]['param']] = array();
			}

			if($nodes[0]['var']) {
				$doc[$nodes[0]['param']][$nodes[0]['var']] = array('param' => $nodes[0]['param'], 'type' => explode('|', $nodes[0]['type']), 'var' => $nodes[0]['var'], 'desc' => $nodes[0]['desc']);
			}
			else {
				$doc[$nodes[0]['param']] = explode('|', $nodes[0]['type']);
			}
		}

		return $doc;
	}

	protected function buildPackages() {
		foreach($this->doc as $class) {
			if(!isset($this->packages[$class['package']])) {
				$this->packages[$class['package']] = array(
					'name' => $class['package'],
					'classes' => array()
				);
			}

			$this->packages[$class['package']]['classes'][] = array(
				'name' => $class['name'],
				'desc' => $class['desc']
			);
		}

		foreach($this->packages as &$package) {
			if($package['classes']) {
				usort($package['classes'], array($this, 'sortPackages'));
			}

			unset($package);
		}

		usort($this->packages, array($this, 'sortPackages'));
	}

	protected function sortPackages($a, $b) {
		return $a['name'] > $b['name'] ? 1 : -1;
	}
}
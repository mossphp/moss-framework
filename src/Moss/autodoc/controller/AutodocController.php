<?php
namespace moss\autodoc\controller;

use moss\container\ContainerInterface;
use moss\http\response\Response;
use moss\autodoc\parser\Markdown;
use moss\component\cache\FileCache;

/**
 * Generates documentation based on PHPDoc comments
 *
 * @package AutoDoc
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class AutodocController {

	private $Container;

	/**
	 * @param ContainerInterface $Container
	 */
	public function __construct(ContainerInterface $Container) {
		$this->Container = & $Container;
	}

	/**
	 * @return Response
	 */
	public function indexAction() {
		$Cache = new FileCache('../cache/');

//		if($autodocResponse = $Cache->fetch('autodocResponse')) {
//			return $autodocResponse;
//		}

		$manDirs = array('../docs');
		$docDirs = array('../moss/');

		$doc = $this->buildDocumentation($manDirs);
		$com = $this->buildComment($docDirs);
		$pck = $this->buildPackages($com);

		$autodocResponseContent = $this->Container
			->get('View')
			->template('moss:autodoc:autodoc')
			->set('Documentation', $doc)
			->set('Comments', $com)
			->set('Packages', $pck)
			->render();

		$autodocResponse = new Response($autodocResponseContent);
		$autodocResponse->makePublic();
		$autodocResponse->setHeader('Cache-control', 'max-age=9200');
		$Cache->store('autodocResponse', $autodocResponse, 9200);

		return $autodocResponse;
	}

	/**
	 * @param array $dirs
	 *
	 * @return array
	 */
	public function buildDocumentation($dirs) {
		$doc = array();

		$MD = new Markdown();

		foreach($dirs as $dir) {
			$RecursiveIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));

			foreach($RecursiveIterator as $item) {
				$chapter = array();
				$content = $MD->transform(file_get_contents((string) $item));

				$content = html_entity_decode($content);

				$d = preg_split('/^(<h[0-2]>.+<\/h[0-2]>)$/im', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
				foreach($d as $k => $v) {
					if(preg_match('/^<h([0-2])>([^<]+)/i', $v, $m)) {
						if($m[1] == 1) {
							$chapter = array(
								'id' => $this->strip($m[2]),
								'name' => $m[2],
								'content' => isset($d[$k+1]) ? $d[$k+1] : '',
								'section' => array()
							);
							continue;
						}

						if($m[1] == 2) {
							$chapter['section'][] = array(
								'id' => $this->strip($m[2]),
								'name' => $m['2'],
								'content' => isset($d[$k+1]) ? $d[$k+1] : ''
							);
						}
					}
				}

				$doc[(string) $item] = $chapter;
			}
		}

		ksort($doc);

		return $doc;
	}

	/**
	 * Builds API documentation based on phpDoc
	 *
	 * @param array $dirs
	 *
	 * @return array
	 */
	public function buildComment($dirs) {
		$doc = array();
		foreach($dirs as $dir) {
			$RecursiveIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));

			foreach($RecursiveIterator as $item) {
				if(!$name = $this->identify($item)) {
					continue;
				}

				include_once((string) $item);
				$doc[$name] = $this->buildClassDoc(new \ReflectionClass($name));
			}
		}

		$namespaces = array_keys($doc);
		foreach($namespaces as &$node) {
			$node = substr($node, 0, strrpos($node, '\\') + 1);
			unset($node);
		}
		$namespaces = array_unique($namespaces);

		foreach($doc as &$class) {
			foreach($class['methods'] as &$method) {
				foreach($method['arguments'] as &$argument) {
					$argument['type'] = $this->repairTypes($doc, $argument['type'], $class['namespace'], $namespaces);
					unset($argument);
				}
				unset($method);
			}
			unset($class);
		}

		usort($doc, function ($a, $b) {
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
		});

		return $doc;
	}

	/**
	 * Identifies interface or class in file, returns namespaced name or null
	 *
	 * @param \SplFileInfo $file
	 *
	 * @return null|string
	 */
	private function identify(\SplFileInfo $file) {
		if(!$file->isFile()) {
			return null;
		}

		if(!preg_match('/^.*\.php$/', (string) $file)) {
			return null;
		}

		$content = file_get_contents((string) $file, null, null, 0, 1024);

		preg_match_all('/^namespace (.+);/im', $content, $nsMatches);
		preg_match_all('/^(abstract )?(interface|class) ([^ ]+).*$/im', $content, $nameMatches);

		if(!empty($nameMatches[3][0])) {
			return empty($nsMatches[1][0]) ? null : $nsMatches[1][0] . '\\' . $nameMatches[3][0];
		}

		return null;
	}

	/**
	 * Builds single class API on its phpDoc
	 *
	 * @param \ReflectionClass $RefClass
	 *
	 * @return array
	 */
	private function buildClassDoc(\ReflectionClass $RefClass) {
		$doc = array(
			'id' => $this->strip($RefClass->getNamespaceName() . '\\' . basename($RefClass->getName())),
			'desc' => $this->commentDesc($RefClass->getDocComment(), true),
			'author' => $this->commentDesc($RefClass->getDocComment(), 'author'),
			'package' => $this->commentDesc($RefClass->getDocComment(), 'package'),
			'name' => $RefClass->getName(),
			'namespace' => $RefClass->getNamespaceName() . '\\',
			'parent' => $RefClass->getParentClass() ? '\\' . $RefClass
					->getParentClass()
					->getName() : null,
			'interfaces' => $RefClass->getInterfaceNames(),
			'properties' => array(),
			'methods' => array(),
			'isAbstract' => $RefClass->isAbstract(),
			'isInterface' => $RefClass->isInterface()
		);

		/** @var \ReflectionProperty $property */
		foreach($RefClass->getProperties() as $property) {
			if(!$property->isPublic()) {
				continue;
			}

			$doc['properties'][] = $property;
		}

		/** @var \ReflectionMethod $method */
		foreach($RefClass->getMethods() as $method) {
			$doc['methods'][] = $this->methodDoc($method);
		}

		return $doc;
	}

	/**
	 * Builds single method description from its phpDoc
	 *
	 * @param \ReflectionMethod $RefMethod
	 *
	 * @return array
	 */
	private function methodDoc(\ReflectionMethod $RefMethod) {
		$doc = array(
			'id' => $this->strip($RefMethod->getDeclaringClass()->getNamespaceName() . '\\' . basename($RefMethod->getDeclaringClass()->getName()) . '::' . $RefMethod->getName()),
			'desc' => $this->commentDesc($RefMethod->getDocComment(), true),
			'doc' => $this->commentParameters($RefMethod->getDocComment()),
			'name' => $RefMethod->getName(),
			'isAbstract' => $RefMethod->isAbstract(),
			'isStatic' => $RefMethod->isStatic(),
			'isPublic' => $RefMethod->isPublic(),
			'isPrivate' => $RefMethod->isPrivate(),
			'isUserDefined' => $RefMethod->isUserDefined(),
			'arguments' => array()
		);

		foreach($RefMethod->getParameters() as $parameter) {
			$doc['arguments']['$' . $parameter->getName()] = $this->parameterDoc($parameter, $doc['doc']);
		}

		return $doc;
	}

	/**
	 * Builds methods argument description from its phpDoc
	 *
	 * @param \ReflectionParameter $RefParameter
	 * @param                      $comment
	 *
	 * @return array
	 */
	private function parameterDoc(\ReflectionParameter $RefParameter, $comment) {
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

	/**
	 * Strips comment
	 *
	 * @param string $comment
	 *
	 * @return string
	 */
	private function comment($comment) {
		$comment = preg_replace('#[ \t]*(?:\/\*\*|\*\/|\*)?[ ]{0,1}(.*)?#', '$1', $comment);
		$comment = str_replace(array("\t", "\r", "\n"), array(null, null, ' '), $comment);
		$comment = str_replace('  ', ' ', $comment);
		$comment = trim($comment);

		return $comment;
	}

	/**
	 * Returns part from comment description
	 *
	 * @param string $comment
	 * @param string $stripParams
	 *
	 * @return null|string
	 */
	private function commentDesc($comment, $stripParams = 'params') {
		$comment = $this->comment($comment);

		if($stripParams === 'author') {
			return stripos($comment, '@author') !== false ? trim(preg_replace('/^.*@author([^>]+>?).*$/', '$1', $comment)) : null;
		}

		if($stripParams === 'package') {
			return stripos($comment, '@package') !== false ? trim(preg_replace('/^.*@package([^@]+).*$/', '$1', $comment)) : null;
		}

		if($stripParams == 'params') {
			return trim(preg_replace('/^([^@]*).*/i', '$1', $comment));
		}

		return $comment;
	}

	/**
	 * Splits phpDoc into param description
	 *
	 * @param string $comment
	 *
	 * @return array
	 */
	private function commentParameters($comment) {
		$doc = array();
		$comment = $this->comment($comment);

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

	/**
	 * Repairs param type found in doc
	 *
	 * @param array  $doc
	 * @param string $type
	 * @param string $namespace
	 * @param array  $namespaces
	 *
	 * @return array|null|string
	 */
	private function repairTypes(&$doc, $type, $namespace, $namespaces) {
		if($type === null) {
			return null;
		}

		if(is_array($type)) {
			foreach($type as &$node) {
				$node = $this->repairTypes($doc, $node, $namespace, $namespaces);
				unset($node);
			}

			return $type;
		}

		if(isset($doc[$namespace . $type])) {
			return $namespace . $type;
		}

		foreach($namespaces as $namespace) {
			if(isset($doc[$namespace . $type])) {
				return $namespace . $type;
			}
		}

		if(preg_match('/^(null|mixed|bool|boolean|int|integer|float|double|string|array|closure|object).*$/i', $type)) {
			return $type;
		}

		return $type;
	}

	/**
	 * Builds package list from phpDoc API
	 *
	 * @param array $doc
	 *
	 * @return array
	 */
	private function buildPackages($doc) {
		$packages = array();

		foreach($doc as $class) {
			if(!isset($packages[$class['package']])) {
				$packages[$class['package']] = array(
					'id' => $this->strip($class['package']),
					'name' => $class['package'],
					'classes' => array()
				);
			}

			$packages[$class['package']]['classes'][] = array(
				'id' => $this->strip($class['name']),
				'name' => $class['name'],
				'desc' => $class['desc']
			);
		}

		foreach($packages as &$package) {
			if($package['classes']) {
				usort($package['classes'], array($this, 'sortPackages'));
			}

			unset($package);
		}

		usort($packages, array($this, 'sortPackages'));

		return $packages;
	}

	/**
	 * Sorts nodes by its name property
	 *
	 * @param array $a
	 * @param array $b
	 *
	 * @return int
	 */
	private function sortPackages($a, $b) {
		return $a['name'] > $b['name'] ? 1 : -1;
	}

	/**
	 * Strips string from non ASCII chars
	 *
	 * @param string $urlString string to strip
	 * @param string $separator char replacing non ASCII chars
	 *
	 * @return string
	 */
	protected function strip($urlString, $separator = '-') {
		$urlString = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $urlString);
		$urlString = strtolower($urlString);
		$urlString = preg_replace('#[^\w\-\.]+#i', $separator, $urlString);
		$urlString = trim($urlString, '-.');

		return $urlString;
	}
}
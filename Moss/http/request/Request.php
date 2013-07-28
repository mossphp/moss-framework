<?php
namespace moss\http\request;

use moss\http\request\RequestInterface;
use moss\http\cookie\CookieInterface;
use moss\http\session\SessionInterface;

/**
 * Request representation
 *
 * @package  Moss HTTP
 * @author   Michal Wachowski <wachowski.michal@gmail.com>
 */
class Request implements RequestInterface {

	private $controller;
	private $locale;
	private $format;

	private $dir;
	private $url;
	private $baseName;

	private $server;
	private $header;
	private $language;
	private $query;
	private $post;
	private $file;

	/** @var SessionInterface|\ArrayAccess|array */
	private $session;

	/** @var CookieInterface|\ArrayAccess|array */
	private $cookie;

	/**
	 * Constructor
	 *
	 * @param SessionInterface $Session
	 * @param CookieInterface  $Cookie
	 */
	public function __construct(SessionInterface $Session = null, CookieInterface $Cookie = null) {
		$this->removeSlashes();

		if($Session === null) {
			$Session = & $_SESSION;
		}

		if($Cookie === null) {
			$Cookie = & $_COOKIE;
		}

		$this->session = & $Session;
		$this->cookie = & $Cookie;
		$this->server = & $_SERVER;

		$this->header = $this->resolveHeaders();
		$this->language = $this->resolveLanguages();

		$this->query = $this->resolveGET();
		$this->post = $this->resolvePOST();
		$this->file = $this->resolveFILES();

		if(!empty($this->query['controller'])) {
			$this->controller($this->query['controller']);
		}

		if(!empty($this->query['locale'])) {
			$this->locale($this->query['locale']);
		}

		if(!empty($this->query['format'])) {
			$this->format($this->query['format']);
		}

		$this->dir = $this->server['PHP_SELF'];
		if(!in_array($this->dir[strlen($this->dir) - 1], array('/', '\\'))) {
			$this->dir = str_replace('\\', '/', dirname($this->dir));
		}

		if(isset($this->server['REQUEST_URI'])) {
			$this->url = $this->dir == '/' ? $this->server['REQUEST_URI'] : preg_replace('/^' . preg_quote($this->dir, '/') . '/', null, $this->server['REQUEST_URI']);
			$this->url = '/' . trim($this->url, '/');

			if(!empty($this->server['REDIRECT_URL'])) {
				$nodes = explode('/', trim($this->dir, '/'));
				$redirect = explode('/', trim($this->server['REDIRECT_URL'], '/'));

				$path = array();
				foreach($nodes as $node) {
					if(!in_array($node, $redirect)) {
						$path[] = $node;
					}
				}

				$invalidRedirect = implode('/', $path);
				if(!empty($invalidRedirect)) {
					$this->dir = substr($this->dir, 0, strpos($this->dir, $invalidRedirect));
					$this->url = (string) substr($this->url, strlen($this->dir) - 1);
				}
			}

			$this->baseName = strtolower(substr($this->schema(), 0, strpos($this->schema(), '/'))) . '://' . str_replace('//', '/', $this->host() . $this->dir . '/');
		}

		$queryStart = strpos($this->url, '?');
		if($queryStart !== false) {
			$this->url = substr($this->url, 0, $queryStart);
		}
	}

	/**
	 * Removes slashes from POST, GET and COOKIE
	 *
	 * @return Request
	 */
	protected function removeSlashes() {
		if(version_compare(phpversion(), '6.0.0-dev', '<') && get_magic_quotes_gpc()) {
			$_POST = array_map(array($this, 'removeSlashed'), $_POST);
			$_GET = array_map(array($this, 'removeSlashed'), $_GET);
			$_COOKIE = array_map(array($this, 'removeSlashed'), $_COOKIE);
		}

		return $this;
	}

	/**
	 * Removes slashes from string
	 *
	 * @param array|string $value
	 *
	 * @return array|string
	 */
	protected function removeSlashed($value) {
		if(is_array($value)) {
			return array_map(array($this, 'removeSlashed'), $value);
		}

		return stripslashes($value);
	}

	/**
	 * Resolves headers data
	 *
	 * @return array
	 */
	protected function resolveHeaders() {
		$parameters = array_merge($_GET, $_POST, $_SERVER);
		$headers = array();

		foreach($parameters as $key => $value) {
			if(strpos($key, 'HTTP_') === 0) {
				$headers[substr($key, 5)] = $value;
			}
			elseif(in_array($key, array('CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'))) {
				$headers[$key] = $value;
			}
		}

		if(isset($parameters['PHP_AUTH_USER'])) {
			$headers['PHP_AUTH_USER'] = $parameters['PHP_AUTH_USER'];
			$headers['PHP_AUTH_PW'] = isset($parameters['PHP_AUTH_PW']) ? $parameters['PHP_AUTH_PW'] : '';
		}
		else {
			$authorizationHeader = null;
			if(isset($parameters['HTTP_AUTHORIZATION'])) {
				$authorizationHeader = $parameters['HTTP_AUTHORIZATION'];
			}
			elseif(isset($parameters['REDIRECT_HTTP_AUTHORIZATION'])) {
				$authorizationHeader = $parameters['REDIRECT_HTTP_AUTHORIZATION'];
			}

			if($authorizationHeader !== null && stripos($authorizationHeader, 'basic') === 0) {
				$exploded = explode(':', base64_decode(substr($authorizationHeader, 6)));
				if(count($exploded) == 2) {
					list($headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']) = $exploded;
				}
			}
		}

		if(isset($headers['PHP_AUTH_USER'])) {
			$headers['AUTHORIZATION'] = 'Basic ' . base64_encode($headers['PHP_AUTH_USER'] . ':' . $headers['PHP_AUTH_PW']);
		}

		return array_change_key_case($headers, CASE_LOWER);
	}

	/**
	 * Resolves query data from $_GET or CLI
	 *
	 * @return array
	 */
	protected function resolveGET() {
		$arr = array();

		if($this->method() == 'CLI' && isset($argc, $argv) && $argc > 1) {
			$this->url = $argv[1];

			for($i = 1; $i < $argc; $i++) {
				$arg = explode('=', $argv[$i]);
				$arr[ltrim($arg[0], '--')] = isset($arg[1]) ? $arg[1] : null;
			}
		}

		return array_merge($_GET, $arr);
	}

	/**
	 * Resolves post data from $_POST or php://input if PUT/DELETE
	 *
	 * @return array
	 */
	protected function resolvePOST() {
		$arr = array();

		if($this->method() == 'PUT' || $this->method() == 'DELETE') {
			parse_str(file_get_contents('php://input'), $arr);
		}

		return array_merge($_POST, $arr);
	}

	/**
	 * Resolves files data from $_FILES
	 *
	 * @return array
	 */
	protected function resolveFiles() {
		$fields = array('name', 'type', 'tmp_name', 'error', 'size');

		$files = array();
		foreach($_FILES as $field => $data) {
			foreach($fields as $property) {
				$this->getFilesProperty($files[$field], $property, $data[$property]);
			}
		}

		return $files;
	}

	/**
	 * Adds node to new files array
	 *
	 * @param array        $result
	 * @param string       $property
	 * @param array|string $node
	 */
	protected function getFilesProperty(&$result, $property, $node) {
		if(is_array($node)) {
			foreach($node as $key => $value) {
				$this->getFilesProperty($result[$key], $property, $value);
			}

			return;
		}

		if($property !== 'error') {
			$result[$property] = $node;

			return;
		}

		$result[$property] = $node;
		switch($node) {
			case 0:
				$result['error_text'] = null;
				break;
			case 1:
				$result['error_text'] = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
				break;
			case 2:
				$result['error_text'] = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
				break;
			case 3:
				$result['error_text'] = 'The uploaded file was only partially uploaded.';
				break;
			case 4:
				$result['error_text'] = 'No file was uploaded.';
				break;
			case 6:
				$result['error_text'] = 'Missing a temporary folder.';
				break;
			case 7:
				$result['error_text'] = 'Failed to write file to disk.';
				break;
			case 8:
				$result['error_text'] = 'A PHP extension stopped the file upload.';
				break;
			default:
				$result['error_text'] = 'Unknown error occurred.';
		}
	}

	/**
	 * Retrieves language codes in quality order
	 * Builds array containing two letter language codes sorted by quality codes
	 *
	 * @return array
	 */
	protected function resolveLanguages() {
		$languages = array();

		if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			return array();
		}

		$codes = $this->splitHttpAcceptHeader($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		foreach($codes as $lang) {
			if(strstr($lang, '-')) {
				$codes = explode('-', $lang);
				$lang = strtolower($codes[0]);
			}

			if(in_array($lang, $languages)) {
				continue;
			}

			$languages[] = $lang;
		}

		if($this->locale() === null) {
			$this->locale($languages[0]);
		}

		return $languages;
	}

	/**
	 * Splits accept header data and sorts by quality
	 *
	 * @param string $header
	 *
	 * @return array
	 */
	protected function splitHttpAcceptHeader($header) {
		if(!$header) {
			return array();
		}

		$values = array();
		foreach(array_filter(explode(',', $header)) as $value) {
			if(preg_match('/;\s*(q=.*$)/', $value, $match)) {
				$quality = (float) substr(trim($match[1]), 2) * 10;
				$value = trim(substr($value, 0, -strlen($match[0])));
			}
			else {
				$quality = 1;
			}

			if(0 < $quality) {
				$values[$quality] = trim($value);
			}
		}

		rsort($values);
		reset($values);

		return $values;
	}

	/**
	 * Returns session instance
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return SessionInterface
	 */
	public function session($key, $default = null) {
		return $this->getFromArray($this->session, explode('.', $key), $default);
	}

	/**
	 * Returns cookie instance
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return CookieInterface
	 */
	public function cookie($key, $default = null) {
		return $this->getFromArray($this->cookie, explode('.', $key), $default);
	}

	/**
	 * Returns server param value for given key or null if key does not exists
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return null|string
	 */
	public function server($key, $default = null) {
		if(!isset($this->server[$key])) {
			return $default;
		}

		return $this->server[$key];
	}

	/**
	 * Returns header value for given key or null if key does not exists
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return null|string
	 */
	public function header($key, $default = null) {
		if(!isset($this->header[$key])) {
			return $default;
		}

		return $this->header[$key];
	}

	/**
	 * Returns query value for given key or null if key does not exists
	 *
	 * @param string $key
	 * @param string $default
	 *
	 * @return null|string
	 */
	public function query($key, $default = null) {
		return $this->getFromArray($this->query, explode('.', $key), $default);
	}

	/**
	 * Returns post value for given key or null if key does not exists
	 *
	 * @param string $key
	 * @param string $default
	 *
	 * @return null|string
	 */
	public function post($key, $default = null) {
		return $this->getFromArray($this->post, explode('.', $key), $default);
	}

	/**
	 * Returns file value for given key or null if key does not exists
	 *
	 * @param string $key
	 *
	 * @return null|string
	 */
	public function file($key) {
		return $this->getFromArray($this->file, explode('.', $key));
	}

	/**
	 * Returns array element matching key
	 *
	 * @param array  $arr
	 * @param array  $keys
	 * @param string $default
	 *
	 * @return string
	 */
	protected function getFromArray(&$arr, $keys, $default = null) {
		$k = array_shift($keys);
		if(!isset($arr[$k])) {
			return $default;
		}

		if(empty($keys)) {
			return $arr[$k];
		}

		return $this->getFromArray($arr[$k], $keys, $default);
	}

	/**
	 * Returns true if request is made via XHR
	 *
	 * @return bool
	 */
	public function isAjax() {
		return !empty($this->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
	}

	/**
	 * Returns request method
	 *
	 * @return string
	 */
	public function method() {
		if(empty($this->server['REQUEST_METHOD'])) {
			return 'CLI';
		}

		return strtoupper($this->server['REQUEST_METHOD']);
	}

	/**
	 * Returns request protocol
	 *
	 * @return null|string
	 */
	public function schema() {
		return empty($this->server['SERVER_PROTOCOL']) ? null : $this->server['SERVER_PROTOCOL'];
	}

	/**
	 * Returns requested host
	 *
	 * @return null|string
	 */
	public function host() {
		return empty($this->server['HTTP_HOST']) ? null : $this->server['HTTP_HOST'];
	}

	/**
	 * Returns requested directory
	 *
	 * @return string
	 */
	public function dir() {
		return $this->dir;
	}

	/**
	 * Returns requested base name (domain+directory)
	 *
	 * @param string $baseName
	 *
	 * @return string
	 */
	public function baseName($baseName = null) {
		if($baseName !== null) {
			$this->baseName = $baseName;
		}

		return $this->baseName;
	}

	/**
	 * Returns client IP address
	 *
	 * @return null|string
	 */
	public function clientIp() {
		if(!empty($this->server['REMOTE_ADDR'])) {
			return $this->server['REMOTE_ADDR'];
		}

		if(!empty($this->server['HTTP_X_FORWARDED_FOR'])) {
			return $this->server['HTTP_X_FORWARDED_FOR'];
		}

		if(!empty($this->server['HTTP_CLIENT_IP'])) {
			return $this->server['HTTP_CLIENT_IP'];
		}

		return null;
	}

	/**
	 * Returns requested controller identifier (if available)
	 *
	 * @param string $controller
	 *
	 * @return null|string
	 */
	public function controller($controller = null) {
		if($controller !== null) {
			$this->controller = $controller;
		}

		return $this->controller;
	}

	/**
	 * Returns requested URL
	 *
	 * @return string
	 */
	public function url() {
		return $this->url;
	}

	/**
	 * Returns address of page which referred user agent (if any)
	 *
	 * @return null|string
	 */
	public function referer() {
		return empty($this->server['HTTP_REFERER']) ? null : $this->server['HTTP_REFERER'];
	}

	/**
	 * Returns locale
	 *
	 * @param null|string $locale
	 *
	 * @return Request
	 */
	public function locale($locale = null) {
		if($locale !== null) {
			$this->locale = $locale;
		}

		if(!empty($this->locale)) {
			return $this->locale;
		}

		if(!empty($this->session['locale'])) {
			return $this->session['locale'];
		}

		if(!empty($this->language[0])) {
			return $this->language[0];
		}

		return null;
	}

	/**
	 * Returns requested format
	 *
	 * @param null|string $format
	 *
	 * @return string
	 */
	public function format($format = null) {
		if($format !== null) {
			$this->format = $format;
		}

		return $this->format;
	}
}
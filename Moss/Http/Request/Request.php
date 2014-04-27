<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Http\Request;

use Moss\Http\Bag\Bag;
use Moss\Http\Bag\BagInterface;
use Moss\Http\Cookie\CookieInterface;
use Moss\Http\Session\SessionInterface;

/**
 * Request representation
 *
 * @package  Moss HTTP
 * @author   Michal Wachowski <wachowski.michal@gmail.com>
 */
class Request implements RequestInterface
{
    private $controller;
    private $locale;
    private $format;

    private $dir;
    private $path;
    private $baseName;

    private $server;
    private $header;
    private $language;

    /**
     * @var BagInterface
     */
    public $query;

    /**
     * @var BagInterface
     */
    public $body;

    /**
     * @var BagInterface
     */
    public $files;

    /**
     * @var SessionInterface
     */
    public $session;

    /**
     * @var CookieInterface
     */
    public $cookie;

    /**
     * Constructor
     *
     * @param SessionInterface $session
     * @param CookieInterface  $cookie
     */
    public function __construct(SessionInterface $session = null, CookieInterface $cookie = null)
    {
        $this->removeSlashes();

        if ($session === null) {
            $session = new Bag();
        }

        if ($cookie === null) {
            $cookie = new Bag();
        }

        $this->session = & $session;
        $this->cookie = & $cookie;

        $this->initialize($_GET, $_POST, $_FILES, $_SERVER);
    }

    /**
     * Initializes request properties
     *
     * @param array $get
     * @param array $post
     * @param array $files
     * @param array $server
     */
    public function initialize(array $get = array(), array $post = array(), array $files = array(), array $server = array())
    {
        $this->server = $server;

        $this->header = $this->resolveHeaders();
        $this->language = $this->resolveLanguages();

        if ($this->locale() === null) {
            $this->locale(reset($this->language));
        }

        $this->dir = $this->resolveDir();
        $this->path = $this->resolvePath();
        $this->baseName = $this->resolveBaseName();

        $this->query = new Bag($this->resolveGET($get));
        $this->body = new Bag($this->resolveBody($post));
        $this->files = new FilesBag($files);

        if (!empty($this->query['controller'])) {
            $this->controller($this->query['controller']);
        }

        if (!empty($this->query['locale'])) {
            $this->locale($this->query['locale']);
        }

        if (!empty($this->query['format'])) {
            $this->format($this->query['format']);
        }
    }

    /**
     * Removes slashes from POST, GET and COOKIE
     *
     * @return Request
     */
    protected function removeSlashes()
    {
        if (version_compare(phpversion(), '6.0.0-dev', '<') && get_magic_quotes_gpc()) {
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
    protected function removeSlashed($value)
    {
        if (is_array($value)) {
            return array_map(array($this, 'removeSlashed'), $value);
        }

        return stripslashes($value);
    }

    /**
     * Resolves headers data
     *
     * @return array
     */
    protected function resolveHeaders()
    {
        $parameters = array_merge($_GET, $_POST, $_SERVER);
        $headers = array();

        foreach ($parameters as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headers[substr($key, 5)] = $value;
            } elseif (in_array($key, array('CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'))) {
                $headers[$key] = $value;
            }
        }

        if (isset($parameters['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER'] = $parameters['PHP_AUTH_USER'];
            $headers['PHP_AUTH_PW'] = isset($parameters['PHP_AUTH_PW']) ? $parameters['PHP_AUTH_PW'] : '';
        } else {
            $authorizationHeader = null;
            if (isset($parameters['HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $parameters['HTTP_AUTHORIZATION'];
            } elseif (isset($parameters['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $parameters['REDIRECT_HTTP_AUTHORIZATION'];
            }

            if ($authorizationHeader !== null && stripos($authorizationHeader, 'basic') === 0) {
                $exploded = explode(':', base64_decode(substr($authorizationHeader, 6)));
                if (count($exploded) == 2) {
                    list($headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']) = $exploded;
                }
            }
        }

        if (isset($headers['PHP_AUTH_USER'])) {
            $headers['AUTHORIZATION'] = 'basic ' . base64_encode($headers['PHP_AUTH_USER'] . ':' . $headers['PHP_AUTH_PW']);
        }

        return array_change_key_case($headers, CASE_LOWER);
    }

    /**
     * Resolves dir
     *
     * @return string
     */
    protected function resolveDir()
    {
        $dir = substr($this->server['SCRIPT_FILENAME'], strlen($this->server['DOCUMENT_ROOT']));
        $dir = str_replace('\\', '/', $dir);
        $dir = '/' . trim(substr($dir, 0, strrpos($dir, '/')), '/') . '/';

        if ($invalidRedirect = $this->resolveInvalidRedirect()) {
            $dir = substr($dir, 0, strpos($dir, $invalidRedirect));
        }

        if (empty($dir) || $dir == '//') {
            return '/';
        }

        return rtrim($dir, '/') . '/';
    }

    /**
     * Resolves invalid redirection path
     *
     * @return string
     */
    protected function resolveInvalidRedirect()
    {
        if (empty($this->server['REDIRECT_URL'])) {
            return false;
        }

        $nodes = substr($this->server['SCRIPT_FILENAME'], strlen($this->server['DOCUMENT_ROOT']));
        $nodes = str_replace('\\', '/', $nodes);
        $nodes = substr($nodes, 0, strrpos($nodes, '/'));
        $nodes = explode('/', trim($nodes, '/'));
        $redirect = explode('/', trim($this->server['REDIRECT_URL'], '/'));

        $path = array();
        foreach ($nodes as $node) {
            if (!in_array($node, $redirect)) {
                $path[] = $node;
            }
        }

        return empty($path) ? false : '/' . implode('/', $path);
    }

    /**
     * Resolves URL
     *
     * @return string
     */
    protected function resolvePath()
    {
        if (!isset($this->server['REQUEST_URI'])) {
            return null;
        }

        $url = $this->server['REQUEST_URI'];

        if (false !== $queryStart = strpos($url, '?')) {
            $url = substr($url, 0, $queryStart);
        }

        $url = preg_replace('/^' . preg_quote($this->dir, '/') . '/', null, $url);
        $url = '/' . trim($url, '/');

        return $url;
    }

    /**
     * Resolves base name
     *
     * @return string
     */
    protected function resolveBaseName()
    {
        $schema = $this->schema();
        $host = str_replace('//', '/', $this->host() . $this->dir . '/');

        return $schema . '://' . $host;
    }

    /**
     * Resolves query data from passed array and CLI
     *
     * @param array $get
     *
     * @return array
     */
    protected function resolveGET(array $get = array())
    {
        if ($this->method() != 'CLI' || !isset($GLOBALS['argc']) || !isset($GLOBALS['argv']) || $GLOBALS['argc'] <= 1) {
            return $get;
        }

        $cli = array();
        for ($i = 1; $i < $GLOBALS['argc']; $i++) {
            if (preg_match_all('/^-+([^=]+)(=(.+))?$/i', $GLOBALS['argv'][$i], $arg, PREG_SET_ORDER)) {
                $cli[$arg[0][1]] = isset($arg[0][3]) ? $this->unquote($arg[0][3]) : true;
            } else {
                $cli[] = $this->unquote($GLOBALS['argv'][$i]);
            }
        }

        if (empty($this->path) && isset($cli[0])) {
            $this->path = array_shift($cli);
        }

        return array_merge($get, $cli);
    }

    protected function unquote(&$val)
    {
        return $val = trim($val, '"\'');
    }

    /**
     * Resolves post data from passed array or php://input if PUT/DELETE
     *
     * @param array $post
     *
     * @return array
     */
    protected function resolveBody(array $post = array())
    {
        $rest = array();

        if (in_array($this->method(), array('OPTIONS', 'HEAD', 'PUT', 'DELETE', 'TRACE'))) {
            parse_str(file_get_contents('php://input'), $rest);
        }

        return array_merge($post, $rest);
    }

    /**
     * Retrieves language codes in quality order
     * Builds array containing two letter language codes sorted by quality codes
     *
     * @return array
     */
    protected function resolveLanguages()
    {
        if (!$this->header('accept_language')) {
            return array();
        }

        $codes = $this->extractHeaders();

        $languages = array();
        foreach ($codes as $lang) {
            if (strstr($lang, '-')) {
                $codes = explode('-', $lang);
                $lang = strtolower($codes[0]);
            }

            if (in_array($lang, $languages)) {
                continue;
            }

            $languages[] = $lang;
        }

        return $languages;
    }

    /**
     * Extracts language codes from header
     *
     * @return array
     */
    protected function extractHeaders()
    {
        $codes = array();
        foreach (array_filter(explode(',', $this->header('accept_language'))) as $value) {
            if (preg_match('/;\s*(q=.*$)/', $value, $match)) {
                $quality = (float) substr(trim($match[1]), 2) * 10;
                $value = trim(substr($value, 0, -strlen($match[0])));
            } else {
                $quality = 1;
            }

            if (0 < $quality) {
                $codes[$quality] = trim($value);
            }
        }

        rsort($codes);

        return $codes;
    }

    /**
     * Returns session value for given key or default if key does not exists
     *
     * @return SessionInterface
     */
    public function session()
    {
        return $this->session;
    }

    /**
     * Returns cookie value for given key or default if key does not exists
     *
     * @return CookieInterface
     */
    public function cookie()
    {
        return $this->cookie;
    }

    /**
     * Returns server param value for given key or null if key does not exists
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return null|string
     */
    public function server($key = null, $default = null)
    {
        return $this->getFromArray($this->server, $key, $default);
    }

    /**
     * Returns header value for given key or null if key does not exists
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return null|string
     */
    public function header($key = null, $default = null)
    {
        return $this->getFromArray($this->header, $key, $default);
    }

    private function getFromArray($array, $key = null, $default = null)
    {
        if ($key === null) {
            return $array;
        }

        if (!isset($array[$key])) {
            return $default;
        }

        return $array[$key];
    }

    /**
     * Returns query values bag
     *
     * @return BagInterface
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * Returns body values bag
     *
     * @return BagInterface
     */
    public function body()
    {
        return $this->body;
    }

    /**
     * Returns files bag
     *
     * @return FilesBag|BagInterface
     */
    public function files()
    {
        return $this->files;
    }

    /**
     * Returns true if request is made via XHR
     *
     * @return bool
     */
    public function isAjax()
    {
        return strtolower($this->header('x_requested_with')) == 'xmlhttprequest';
    }

    /**
     * Returns true if request is made via SSL
     *
     * @return bool
     */
    public function isSecure()
    {
        if ($proto = $this->header('x_forwarded_proto')) {
            return in_array(strtolower(current(explode(',', $proto))), array('https', 'on', 'ssl', '1'));
        }

        return strtolower($this->server('HTTPS')) == 'on' || $this->server('HTTPS') == 1;
    }

    /**
     * Returns request method
     *
     * @return string
     */
    public function method()
    {
        return strtoupper($this->server('REQUEST_METHOD', 'CLI'));
    }

    /**
     * Returns request protocol
     *
     * @return null|string
     */
    public function schema()
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    /**
     * Returns requested host
     *
     * @return null|string
     */
    public function host()
    {
        return $this->header('host');
    }

    /**
     * Returns requested directory
     *
     * @return string
     */
    public function dir()
    {
        return $this->dir;
    }

    /**
     * Returns requested URL
     *
     * @param bool $query
     *
     * @return string
     */
    public function path($query = false)
    {
        return $this->path . ($query && $this->query->has() ? '?' . http_build_query($this->query->all(), null, '&') : null);
    }

    /**
     * Returns requested base name (domain+directory)
     *
     * @param string $baseName
     *
     * @return string
     */
    public function baseName($baseName = null)
    {
        if ($baseName !== null) {
            $this->baseName = $baseName;
        }

        return $this->baseName;
    }

    /**
     * Returns requested URI
     *
     * @param bool $query
     *
     * @return string
     */
    public function uri($query = false)
    {
        return rtrim($this->baseName(), '/') . '/' . ltrim($this->path($query), '/');
    }

    /**
     * Returns client IP address
     *
     * @return null|string
     */
    public function clientIp()
    {
        $keys = array(
            'REMOTE_ADDR',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
        );

        foreach ($keys as $offset) {
            if ($this->server($offset)) {
                return $this->server($offset);
            }
        }

        return $this->server('REMOTE_ADDR');
    }

    /**
     * Returns requested controller identifier (if available)
     *
     * @param string $controller
     *
     * @return null|string
     */
    public function controller($controller = null)
    {
        if ($controller !== null) {
            $this->controller = $controller;
        }

        return $this->controller;
    }

    /**
     * Returns address of page which referred user agent (if any)
     *
     * @return null|string
     */
    public function referrer()
    {
        return empty($this->server['HTTP_REFERER']) ? null : $this->server['HTTP_REFERER'];
    }

    /**
     * Returns locale
     *
     * @param null|string $locale
     *
     * @return Request
     */
    public function locale($locale = null)
    {
        if ($locale !== null) {
            $this->locale = $locale;
        }

        if (!empty($this->locale)) {
            return $this->locale;
        }

        if (!empty($this->session['locale'])) {
            return $this->session['locale'];
        }

        if (!empty($this->language[0])) {
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
    public function format($format = null)
    {
        if ($format !== null) {
            $this->format = $format;
        }

        return $this->format;
    }
}

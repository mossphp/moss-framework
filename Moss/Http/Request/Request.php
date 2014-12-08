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

use Moss\Bag\Bag;
use Moss\Bag\BagInterface;
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
    protected $route;
    protected $locale;
    protected $format;

    protected $dir;
    protected $path;
    protected $baseName;

    /**
     * @var BagInterface
     */
    protected $server;

    /**
     * @var HeaderBag
     */
    protected $header;
    protected $language;

    /**
     * @var BagInterface
     */
    protected $query;

    /**
     * @var BagInterface
     */
    protected $body;

    /**
     * @var BagInterface|FilesBag
     */
    protected $files;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var CookieInterface
     */
    protected $cookie;

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
    public function initialize(array $get = [], array $post = [], array $files = [], array $server = [])
    {
        $this->server = new Bag($server);

        $this->header = new HeaderBag(array_merge($get, $post, $server));
        $this->language = $this->header->languages();

        if ($this->locale() === null) {
            $this->locale(reset($this->language));
        }

        $this->dir = $this->resolveDir();
        $this->path = $this->resolvePath();
        $this->baseName = $this->resolveBaseName();

        $this->query = new Bag($this->resolveParameters($get));
        $this->body = new Bag($this->resolveBody($post));
        $this->files = new FilesBag($files);

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
            $_POST = array_map([$this, 'removeSlashed'], $_POST);
            $_GET = array_map([$this, 'removeSlashed'], $_GET);
            $_COOKIE = array_map([$this, 'removeSlashed'], $_COOKIE);
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
            return array_map([$this, 'removeSlashed'], $value);
        }

        return stripslashes($value);
    }

    /**
     * Resolves dir
     *
     * @return string
     */
    protected function resolveDir()
    {
        $dir = substr($this->server('SCRIPT_FILENAME'), strlen($this->server('DOCUMENT_ROOT')));
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

        $path = [];
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
     * Resolves request parameters from passed array and CLI
     *
     * @param array $get
     *
     * @return array
     */
    protected function resolveParameters(array $get = [])
    {
        if ($this->method() != 'CLI' || !isset($GLOBALS['argc']) || !isset($GLOBALS['argv']) || $GLOBALS['argc'] <= 1) {
            return $get;
        }

        $cli = [];
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

    /**
     * Removes quotes
     *
     * @param string $val
     *
     * @return string
     */
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
    protected function resolveBody(array $post = [])
    {
        $rest = [];

        if (in_array($this->method(), ['OPTIONS', 'HEAD', 'PUT', 'DELETE', 'TRACE'])) {
            parse_str(file_get_contents('php://input'), $rest);
        }

        return array_merge($post, $rest);
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
     * @param null|string $key
     * @param mixed       $default
     *
     * @return null|string
     */
    public function server($key = null, $default = null)
    {
        return $this->server->get($key, $default);
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
        return $this->header->get($key, $default);
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
        return strtolower((string) $this->header('x_requested_with')) == 'xmlhttprequest';
    }

    /**
     * Returns true if request is made via SSL
     *
     * @return bool
     */
    public function isSecure()
    {
        if ($proto = (string) $this->header('x_forwarded_proto')) {
            return in_array(strtolower(current(explode(',', $proto))), ['https', 'on', 'ssl', '1']);
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
     * @return string
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
            $this->baseName = rtrim($baseName, '/') . '/';
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
        $keys = [
            'REMOTE_ADDR',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
        ];

        foreach ($keys as $offset) {
            if ($this->server->has($offset)) {
                return $this->server->get($offset);
            }
        }

        return null;
    }

    /**
     * Returns requested route name (if successfully resolved)
     *
     * @param null|string $route
     *
     * @return string
     */
    public function route($route = null)
    {
        if ($route !== null) {
            $this->route = $route;
        }

        return $this->route;
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
     * Returns languages sorted by quality (priority)
     *
     * @return array
     */
    public function language()
    {
        return $this->language;
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

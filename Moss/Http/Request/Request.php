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
    protected $languages;
    protected $language;

    /**
     * @var BagInterface
     */
    protected $query;

    /**
     * @var BagInterface
     */
    protected $body;

    protected $raw;

    /**
     * @var BagInterface|FilesBag
     */
    protected $files;

    /**
     * @var BagInterface|SessionInterface
     */
    protected $session;

    /**
     * @var BagInterface
     */
    protected $cookie;

    /**
     * Constructor
     *
     * @param array  $get
     * @param array  $post
     * @param array  $files
     * @param array  $server
     * @param array  $cookie
     * @param string $rawBody
     * @param array  $globals
     */
    public function __construct(array $get = [], array $post = [], array $cookie = [], array $files = [], array $server = [], $rawBody = null, array $globals = [])
    {
        $this->initialize($get, $post, $cookie, $files, $server, $rawBody, $globals);
    }

    /**
     * Initializes request properties
     *
     * @param array  $get
     * @param array  $post
     * @param array  $cookie
     * @param array  $files
     * @param array  $server
     * @param string $rawBody
     * @param array  $globals
     */
    public function initialize(array $get = [], array $post = [], array $cookie = [], array $files = [], array $server = [], $rawBody = null, array $globals = [])
    {
        $cookie = $this->removeSlashes($cookie);
        $get = $this->removeSlashes($get);
        $post = $this->removeSlashes($post);

        $this->cookie = new Bag($cookie);

        $this->server = new Bag($server);
        $this->header = new HeaderBag(array_merge($get, $post, $server));

        $this->languages = $this->header->languages();
        $this->language = reset($this->languages);

        $this->dir = $this->resolveDir();
        $this->path = $this->resolvePath();
        $this->baseName = $this->resolveBaseName();

        $this->query = new Bag($this->resolveParameters($get, $globals));

        $this->raw = (string) $rawBody;
        $this->body = new Bag($this->resolveBody($post));

        $this->files = new FilesBag($files);

        if (!empty($this->query['locale'])) {
            $this->language($this->query['locale']);
        }

        if (!empty($this->query['format'])) {
            $this->format($this->query['format']);
        }
    }

    /**
     * Removes slashes from array
     *
     * @param array $array
     *
     * @return array
     */
    protected function removeSlashes($array)
    {
        $fnc = function ($value) use (&$fnc) {
            if (is_array($value)) {
                return array_map($fnc, $value);
            }

            return stripslashes($value);
        };

        if (version_compare(phpversion(), '6.0.0-dev', '<') && get_magic_quotes_gpc()) {
            $array = array_map($fnc, $array);
        }

        return $array;
    }

    /**
     * Resolves dir
     *
     * @return string
     */
    protected function resolveDir()
    {
        $dir = substr($this->server->get('SCRIPT_FILENAME'), strlen($this->server->get('DOCUMENT_ROOT')));
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
     * @param array $globals
     *
     * @return array
     */
    protected function resolveParameters(array $get = [], array $globals = [])
    {
        if ($this->method() != 'CLI' || !isset($globals['argc'], $globals['argv']) || $globals['argc'] <= 1) {
            return $get;
        }

        $cli = [];
        for ($i = 1; $i < $globals['argc']; $i++) {
            if (preg_match_all('/^-+([^=]+)(=(.+))?$/i', $globals['argv'][$i], $arg, PREG_SET_ORDER)) {
                $cli[$arg[0][1]] = isset($arg[0][3]) ? $this->unquote($arg[0][3]) : true;
            } else {
                $cli[] = $this->unquote($globals['argv'][$i]);
            }
        }

        $this->path = array_shift($cli);

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
            parse_str($this->raw, $rest);
        }

        return array_merge($post, $rest);
    }

    /**
     * Returns bag with cookie properties
     *
     * @return BagInterface
     */
    public function cookie()
    {
        return $this->cookie;
    }

    /**
     * Returns bag with server properties
     *
     * @return BagInterface
     */
    public function server()
    {
        return $this->server;
    }

    /**
     * Returns bag with headers
     *
     * @return BagInterface
     */
    public function header()
    {
        return $this->header;
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
     * Returns raw body content
     *
     * @return string
     */
    public function rawBody()
    {
        return $this->raw;
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
        return strtolower((string) $this->header->get('x_requested_with')) == 'xmlhttprequest';
    }

    /**
     * Returns true if request is made via SSL
     *
     * @return bool
     */
    public function isSecure()
    {
        if ($proto = (string) $this->header->get('x_forwarded_proto')) {
            return in_array(strtolower(current(explode(',', $proto))), ['https', 'on', 'ssl', '1']);
        }

        return strtolower($this->server->get('HTTPS')) == 'on' || $this->server->get('HTTPS') == 1;
    }

    /**
     * Returns request method
     *
     * @return string
     */
    public function method()
    {
        return strtoupper($this->server->get('REQUEST_METHOD', 'CLI'));
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
        return (string) $this->header->get('host');
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
    public function languages()
    {
        return $this->languages;
    }

    /**
     * Returns language
     *
     * @param null|string $locale
     *
     * @return Request
     */
    public function language($locale = null)
    {
        if ($locale !== null) {
            $this->language = $locale;
        }

        return $this->language;
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

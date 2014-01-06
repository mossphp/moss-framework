<?php
namespace moss\http\request;

use moss\http\bag\Bag;
use moss\http\bag\BagInterface;
use moss\http\cookie\CookieInterface;
use moss\http\session\SessionInterface;

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
    private $url;
    private $baseName;

    private $server;
    private $header;
    private $language;

    /** @var BagInterface */
    public $query;

    /** @var BagInterface */
    public $post;

    /** @var BagInterface */
    public $files;

    /** @var SessionInterface */
    public $session;

    /** @var CookieInterface */
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
            $session = new Bag($_SESSION);
        }

        if ($cookie === null) {
            $cookie = new Bag($_COOKIE);
        }

        $this->session = & $session;
        $this->cookie = & $cookie;
        $this->server = & $_SERVER;

        $this->header = $this->resolveHeaders();
        $this->language = $this->resolveLanguages();

        if ($this->locale() === null) {
            $this->locale(reset($this->language));
        }

        $this->dir = substr($this->server['SCRIPT_FILENAME'], strlen($this->server['DOCUMENT_ROOT']));
        $this->dir = str_replace('\\', '/', $this->dir);
        $this->dir = '/' . trim(substr($this->dir, 0, strrpos($this->dir(), '/')), '/');

        if (isset($this->server['REQUEST_URI'])) {
            $this->resolveUrl();
        }

        $this->query = new Bag($this->resolveGET());
        $this->post = new Bag($this->resolvePOST());
        $this->files = new FilesBag($_FILES);

        if (!empty($this->query['controller'])) {
            $this->controller($this->query['controller']);
        }

        if (!empty($this->query['locale'])) {
            $this->locale($this->query['locale']);
        }

        if (!empty($this->query['format'])) {
            $this->format($this->query['format']);
        }

        $queryStart = strpos($this->url, '?');
        if ($queryStart !== false) {
            $this->url = substr($this->url, 0, $queryStart);
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
     * Resolves URL
     */
    protected function resolveUrl()
    {
        if ($this->dir == '/') {
            $this->url = $this->server['REQUEST_URI'];
        } else {
            $this->url = preg_replace('/^' . preg_quote($this->dir, '/') . '/', null, $this->server['REQUEST_URI']);
        }

        $this->url = '/' . trim($this->url, '/');

        if (!empty($this->server['REDIRECT_URL'])) {
            $nodes = explode('/', trim($this->dir, '/'));
            $redirect = explode('/', trim($this->server['REDIRECT_URL'], '/'));

            $path = array();
            foreach ($nodes as $node) {
                if (!in_array($node, $redirect)) {
                    $path[] = $node;
                }
            }

            $invalidRedirect = implode('/', $path);
            if (!empty($invalidRedirect)) {
                $this->dir = substr($this->dir, 0, strpos($this->dir, $invalidRedirect));
                $this->url = (string) substr($this->url, strlen($this->dir) - 1);
            }
        }

        $schema = $this->schema();
        $host = str_replace('//', '/', $this->host() . $this->dir . '/');

        $this->baseName = $schema . '://' . $host;
    }

    /**
     * Resolves query data from $_GET or CLI
     *
     * @return array
     */
    protected function resolveGET()
    {
        $cli = array();

        if ($this->method() == 'CLI' && isset($GLOBALS['argc']) && isset($GLOBALS['argv']) && $GLOBALS['argc'] > 1) {
            for ($i = 1; $i < $GLOBALS['argc']; $i++) {
                if (strpos($GLOBALS['argv'][$i], '=') === false) {
                    $cli[$i - 1] = $GLOBALS['argv'][$i];
                    continue;
                }

                $arg = explode('=', $GLOBALS['argv'][$i]);
                $cli[ltrim($arg[0], '-')] = isset($arg[1]) ? $arg[1] : null;
            }

            if (empty($this->url) && isset($cli[0])) {
                $this->url = array_shift($cli);
            }
        }

        return array_merge($_GET, $cli);
    }

    /**
     * Resolves post data from $_POST or php://input if PUT/DELETE
     *
     * @return array
     */
    protected function resolvePOST()
    {
        $rest = array();

        if ($this->method() == 'PUT' || $this->method() == 'DELETE') {
            parse_str(file_get_contents('php://input'), $rest);
        }

        return array_merge($_POST, $rest);
    }

    /**
     * Retrieves language codes in quality order
     * Builds array containing two letter language codes sorted by quality codes
     *
     * @return array
     */
    protected function resolveLanguages()
    {
        $languages = array();

        if (!$this->header('accept_language')) {
            return array();
        }

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
        if ($key === null) {
            return $this->server;
        }

        if (!isset($this->server[$key])) {
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
    public function header($key = null, $default = null)
    {
        if ($key === null) {
            return $this->header;
        }

        if (!isset($this->header[$key])) {
            return $default;
        }

        return $this->header[$key];
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
     * Returns post values bag
     *
     * @return BagInterface
     */
    public function post()
    {
        return $this->post;
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
     * Returns client IP address
     *
     * @return null|string
     */
    public function clientIp()
    {
        if ($this->server('REMOTE_ADDR')) {
            return $this->server('REMOTE_ADDR');
        }

        if ($this->server('HTTP_X_FORWARDED_FOR')) {
            return $this->server('HTTP_X_FORWARDED_FOR');
        }

        return $this->server('HTTP_CLIENT_IP');
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
     * Returns requested URI
     *
     * @param bool $query
     *
     * @return string
     */
    public function uri($query = false)
    {
        return $this->baseName() . $this->path($query);
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
        return $this->url . ($query && $this->query->has() ? '?' . http_build_query($this->query->all(), null, '&') : null);
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

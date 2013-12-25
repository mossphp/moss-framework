<?php
namespace moss\http\session;

use moss\http\bag\Bag;

/**
 * Session object representation
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Session extends Bag implements SessionInterface
{


    /**
     * Creates session wrapper instance
     * Also validates existing session - if session is invalid, resets it
     *
     * @param string $name
     * @param string $cacheLimiter
     */
    public function __construct($name = 'PHPSESSID', $cacheLimiter = '')
    {
        $this->name($name);
        $this->cacheLimiter($cacheLimiter);

        if (!$this->identify()) {
            $this->startSession();
        }

        $this->storage = & $_SESSION;
    }

    /**
     * Starts session
     *
     * @throws \RuntimeException
     */
    protected function startSession()
    {
        if (version_compare(phpversion(), '5.4.0', '>=') && \PHP_SESSION_ACTIVE === session_status()) {
            throw new \RuntimeException('Session already started by PHP.');
        }

        if (version_compare(phpversion(), '5.4.0', '<') && isset($_SESSION) && session_id()) {
            throw new \RuntimeException('Session already started by PHP ($_SESSION is set).');
        }

        if (ini_get('session.use_cookies') && headers_sent($file, $line)) {
            throw new \RuntimeException(sprintf('Unable to start session, headers have already been sent by "%s" at line %d.', $file, $line));
        }

        if (!session_start()) {
            throw new \RuntimeException('Unable to start session');
        }
    }

    /**
     * Clears all session data and regenerates session ID
     *
     * @return $this
     */
    public function destroy()
    {
        unset($this->storage);

        $_SESSION = array();
        session_destroy();
        $this->startSession();

        $this->storage = & $_SESSION;
    }

    /**
     * Regenerates the session ID
     *
     * @return $this
     * @throws \RuntimeException
     */
    public function regenerate()
    {
        session_regenerate_id(true);
        session_write_close();

        $backup = $_SESSION;

        if (!session_start()) {
            throw new \RuntimeException('Failed to start the session');
        }

        $_SESSION = $backup;

        $this->storage = & $_SESSION;

        return $this;
    }

    /**
     * Returns session identifier
     *
     * @param string $identifier
     *
     * @return string
     */
    public function identify($identifier = null)
    {
        if ($identifier !== null) {
            session_id($identifier);
        }

        return session_id();
    }

    /**
     * Returns session name
     *
     * @param string $name
     *
     * @return string
     */
    public function name($name = null)
    {
        if ($name !== null) {
            session_name($name);
        }

        return session_name();
    }

    /**
     * Returns session cache limiter
     *
     * @param string $cacheLimiter
     *
     * @return string
     */
    public function cacheLimiter($cacheLimiter = null)
    {
        if ($cacheLimiter !== null) {
            session_cache_limiter($cacheLimiter);
        }

        return session_cache_limiter();
    }
}

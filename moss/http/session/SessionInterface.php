<?php
namespace moss\http\session;

use moss\http\bag\BagInterface;

/**
 * Session objects interface
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface SessionInterface extends BagInterface
{
    /**
     * Regenerates the session ID
     *
     * @return $this
     */
    public function regenerate();

    /**
     * Clears all session data and regenerates session ID
     *
     * @return $this
     */
    public function destroy();

    /**
     * Returns session identifier
     *
     * @param string $identifier
     *
     * @return string
     */
    public function identify($identifier = null);

    /**
     * Returns session name
     *
     * @param string $name
     *
     * @return string
     */
    public function name($name = null);
}

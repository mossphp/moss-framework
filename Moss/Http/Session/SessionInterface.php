<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Http\Session;

use Moss\Bag\BagInterface;

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

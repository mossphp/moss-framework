<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Security;

/**
 * Security token stash interface
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface TokenStashInterface
{

    /**
     * Stashes token
     *
     * @param TokenInterface $token
     *
     * @return $this
     */
    public function put(TokenInterface $token);

    /**
     * Returns stashed token
     *
     * @return TokenInterface
     */
    public function get();

    /**
     * Destroys stashed token
     *
     * @return $this
     */
    public function destroy();
}

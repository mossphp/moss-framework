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

use Moss\Http\Session\SessionInterface;

/**
 * Security token stash interface
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class TokenStash implements TokenStashInterface
{

    /**
     * @var \Moss\Http\Session\SessionInterface
     */
    protected $stash;

    /**
     * Constructor
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->stash = & $session;
    }

    /**
     * Stashes token
     *
     * @param TokenInterface $token
     *
     * @return $this
     */
    public function put(TokenInterface $token)
    {
        $this->stash->regenerate();
        $this->stash->set('token', $token);

        return $this;
    }

    /**
     * Returns stashed token
     *
     * @return null|TokenInterface
     */
    public function get()
    {
        return $this->stash->get('token');
    }

    /**
     * Destroys stashed token
     *
     * @return $this
     */
    public function destroy()
    {
        $this->stash->remove('token');

        return $this;
    }
}

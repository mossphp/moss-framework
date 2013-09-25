<?php
namespace moss\security;

use moss\http\session\SessionInterface;
use moss\security\TokenStashInterface;
use moss\security\TokenInterface;

/**
 * Security token stash interface
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class TokenStash implements TokenStashInterface
{

    /** @var \moss\http\session\SessionInterface */
    protected $stash;

    /**
     * Constructor
     *
     * @param SessionInterface $Session
     */
    public function __construct(SessionInterface $Session)
    {
        $this->stash = & $Session;
    }

    /**
     * Stashes token
     *
     * @param TokenInterface $Token
     *
     * @return $this
     */
    public function put(TokenInterface $Token)
    {
        $this->stash->set('token', $Token);
    }

    /**
     * Returns stashed token
     *
     * @return TokenInterface
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
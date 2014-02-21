<?php
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

    /** @var \Moss\Http\Session\SessionInterface */
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
        $this->stash->set('token', $token);

        return $this;
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

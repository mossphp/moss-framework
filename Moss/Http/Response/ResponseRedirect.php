<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Http\Response;

/**
 * Redirecting Response
 * Response redirects (Status 302) client to given address
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class ResponseRedirect extends Response
{

    protected $delay;
    protected $address;

    /**
     * Constructor
     * Sets redirection address and delay
     *
     * @param string $address redirection address
     * @param int    $delay   redirection delay in seconds
     */
    public function __construct($address, $delay = 0)
    {
        $this->header = new HeaderBag();

        $this->address($address);
        $this->delay($delay);

        $this->content('Redirecting...');
        $this->status('302');
    }

    /**
     * Sends content
     *
     * @return ResponseInterface
     */
    public function sendContent()
    {
        if ($this->delay) {
            echo '<script type="text/javascript" language="javascript">setTimeout("window.location.href = \'' . $this->address . '\'", ' . ($this->delay * 1000) . ');</script>' . $this->content;

            return $this;
        }

        echo $this->content;

        return $this;
    }

    /**
     * Sets redirection address
     *
     * @param null|string $address redirection address
     *
     * @return ResponseRedirect
     */
    public function address($address = null)
    {
        if ($address !== null) {
            $this->address = str_replace('&amp;', '&', $address);
        }

        $this->setRedirectHeaders();

        return $this->address;
    }

    /**
     * Sets redirection delay
     *
     * @param int $delay redirection delay in seconds
     *
     * @return ResponseRedirect
     */
    public function delay($delay = null)
    {
        if ($delay !== null) {
            $this->delay = (int) $delay;
        }

        $this->setRedirectHeaders();

        return $this->delay;
    }

    /**
     * Sets/updated redirect headers
     */
    protected function setRedirectHeaders()
    {
        $this->header->remove('Location');
        $this->header->remove('Refresh');

        if ($this->delay) {
            $this->header->set('Refresh', $this->delay . '; URL=' . $this->address);

            return;
        }

        $this->header->set('Location', $this->address);
    }
}

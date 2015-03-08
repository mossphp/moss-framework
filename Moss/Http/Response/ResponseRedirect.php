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
     * @param int    $status  redirect status
     */
    public function __construct($address, $status = 302)
    {
        parent::__construct('Redirecting...');

        $this->header->all([]);
        $this->address($address);
        $this->status($status);
    }

    /**
     * Sends content
     *
     * @return ResponseInterface
     */
    public function sendContent()
    {
        echo $this->content;

        return $this;
    }

    /**
     * Sets redirection address
     *
     * @param null|string $address redirection address
     *
     * @return string
     */
    public function address($address = null)
    {
        if ($address !== null) {
            $this->address = str_replace('&amp;', '&', $address);
        }

        $this->header->set('Location', $this->address);

        return $this->address;
    }
}

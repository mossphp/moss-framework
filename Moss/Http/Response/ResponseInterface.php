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
 * Response interface
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface ResponseInterface
{

    /**
     * Retrieves header bag
     *
     * @return HeaderBag
     */
    public function header();

    /**
     * Sets response content
     *
     * @param string $content
     *
     * @return ResponseInterface
     */
    public function content($content = null);

    /**
     * Sets response status code
     *
     * @param int $status
     *
     * @return ResponseInterface
     */
    public function status($status);

    /**
     * Sets response protocol and its version
     *
     * @param string $protocol
     *
     * @return ResponseInterface
     */
    public function protocol($protocol = null);

    /**
     * Marsk response as public
     *
     * @return ResponseInterface
     */
    public function makePublic();

    /**
     * Marks response as private
     *
     * @return ResponseInterface
     */
    public function makePrivate();

    /**
     * Sends headers
     *
     * @return ResponseInterface
     */
    public function sendHeaders();

    /**
     * Sends content
     *
     * @return ResponseInterface
     */
    public function sendContent();

    /**
     * Sends response
     *
     * @return ResponseInterface
     */
    public function send();

    /**
     * Casts response into string with headers
     *
     * @return string;
     */
    public function __toString();
}

<?php
namespace moss\http\response;

use moss\http\response\Response;

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
     * Sets redirection address, delay and response content
     *
     * @param string $address redirection address
     * @param int    $delay   redirection delay in seconds
     */
    public function __construct($address, $delay = 0)
    {
        $this->address($address);
        $this->delay($delay);

        $this->content('Redirecting...');
        $this->status('302');
    }

    /**
     * Sends headers
     *
     * @return ResponseInterface
     */
    public function sendHeaders()
    {
        if (headers_sent()) {
            return $this;
        }

        header($this->protocol . ' ' . $this->status . ' ' . $this->statusTexts[$this->status], true, $this->status);

        foreach ($this->headers() as $header => $value) {
            if ($value === null) {
                continue;
            }

            header($header . ': ' . $value);
        }

        return $this;
    }

    /**
     * Sends content
     *
     * @return ResponseInterface
     */
    public function sendContent()
    {
        if (headers_sent() || $this->delay) {
            echo '<script type="text/javascript" language="javascript">setTimeout("window.location.href = \'' . $this->address . '\'", ' . ($this->delay * 1000) . ');</script>' . $this->content;

            return $this;
        }

        echo $this->content;

        return $this;
    }

    /**
     * Sets redirection address
     *
     * @param string $address redirection address
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
        $this->setHeader('Location', null);
        $this->setHeader('Refresh', null);

        if ($this->delay) {
            $this->setHeader('Refresh', $this->delay . '; URL=' . $this->address);

            return;
        }

        $this->setHeader('Location', $this->address);
    }
}

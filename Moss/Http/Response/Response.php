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
 * Response sent to client
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Response implements ResponseInterface
{

    public $header = array();

    protected $content = 'OK';
    protected $status = 200;
    protected $protocol = 'HTTP/1.1';

    protected $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    );

    /**
     * Creates text/html response instance
     *
     * @param string $content
     * @param int    $status
     * @param string $contentType
     */
    public function __construct($content = 'OK', $status = 200, $contentType = 'text/html; charset=UTF-8')
    {
        $this->header = new HeaderBag();

        $this->content($content);
        $this->status($status);

        $this->header->set('Content-Type', $contentType);

        $this->makeNoCache();
    }

    /**
     * Retrieves header bag
     *
     * @return HeaderBag
     */
    public function header()
    {
        return $this->header;
    }

    /**
     * Returns response content
     *
     * @param string $content
     *
     * @return string
     * @throws ResponseException
     */
    public function content($content = null)
    {
        if ($content !== null) {
            if (!is_scalar($content) && !is_callable(array($content, '__toString'))) {
                throw new ResponseException('Response content must be a scalar or object with __toString() method "' . (is_object($content) ? get_class($content) : gettype($content)) . '" given.');
            }

            $this->content = (string) $content;
        }

        return $this->content;
    }

    /**
     * Returns response status code
     *
     * @param int $status
     *
     * @return int
     * @throws ResponseException
     */
    public function status($status = null)
    {
        if ($status !== null) {
            if (!isset($this->statusTexts[$status])) {
                throw new ResponseException('Unsupported status code "' . $status . '"');
            }

            $this->status = (int) $status;

            if ($this->content === null) {
                $this->content = $this->statusTexts[$this->status];
            }
        }

        return $this->status;
    }

    /**
     * Returns response protocol and its version
     *
     * @param string $protocol
     *
     * @return Response|ResponseInterface
     */
    public function protocol($protocol = null)
    {
        if ($protocol !== null) {
            $this->protocol = $protocol;
        }

        return $this->protocol;
    }

    /**
     * Marks response as no-cache
     *
     * @return Response|ResponseInterface
     */
    public function makeNoCache()
    {
        $this->header->set('Cache-Control', 'no-cache');
        $this->header->set('Pragma', 'no-cache');

        return $this;
    }

    /**
     * Marks response as public
     *
     * @return Response|ResponseInterface
     */
    public function makePublic()
    {
        $this->header->set('Cache-Control', 'public');
        $this->header->set('Pragma', 'public');

        return $this;
    }

    /**
     * Marks response as private
     *
     * @return Response|ResponseInterface
     */
    public function makePrivate()
    {
        $this->header->set('Cache-Control', 'private');
        $this->header->set('Pragma', 'private');

        return $this;
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

        foreach ($this->header() as $header => $value) {
            if (empty($value)) {
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
        echo $this->content;

        return $this;
    }

    /**
     * Sends response
     *
     * @return ResponseInterface
     */
    public function send()
    {
        return $this
            ->sendHeaders()
            ->sendContent();
    }

    /**
     * Returns headers and response contents as string
     *
     * @return string;
     */
    public function __toString()
    {
        $headers = '';
        foreach ($this->header as $header => $value) {
            if (empty($value)) {
                continue;
            }

            $headers .= $header . ': ' . $value . "\r\n";
        }

        return $this->protocol . ' ' . $this->status . ' ' . $this->statusTexts[$this->status] . "\r\n" . $headers . "\r\n" . $this->content;
    }
}

<?php
namespace Moss\http\response;

use Moss\http\response\ResponseHeaderBag;
use Moss\http\response\ResponseInterface;
use Moss\http\response\ResponseException;

/**
 * Response sent to client
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Response extends ResponseHeaderBag implements ResponseInterface {

	protected $headers = array();
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
	public function __construct($content = 'OK', $status = 200, $contentType = 'text/html; charset=UTF-8') {
		$this->content($content);
		$this->status($status);
		$this->addHeader('Content-Type: ' . $contentType);
	}

	/**
	 * Returns response content
	 *
	 * @param string $content
	 *
	 * @return string
	 * @throws ResponseException
	 */
	public function content($content = null) {
		if($content !== null) {
			if(!is_scalar($content) && !is_callable(array($content, '__toString'))) {
				throw new ResponseException('Response content must be a scalar or object with __toString() method "' . gettype($content) . '" given.');
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
	public function status($status = null) {
		if($status !== null) {
			if(!isset($this->statusTexts[$status])) {
				throw new ResponseException('Unsupported status code ' . $status);
			}

			$this->status = (int) $status;
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
	public function protocol($protocol = null) {
		if($protocol !== null) {
			$this->protocol = $protocol;
		}

		return $this->protocol;
	}

	/**
	 * Marks response as public
	 *
	 * @return Response|ResponseInterface
	 */
	public function makePublic() {
		$this->removeHeader('Cache-Control: private');
		$this->addHeader('Cache-Control: public');

		$this->removeHeader('Pragma: private');
		$this->addHeader('Pragma: public');

		return $this;
	}

	/**
	 * Marks response as private
	 *
	 * @return Response|ResponseInterface
	 */
	public function makePrivate() {
		$this->removeHeader('Cache-Control: public');
		$this->addHeader('Cache-Control: private');

		$this->removeHeader('Pragma: public');
		$this->addHeader('Pragma: private');

		return $this;
	}

	/**
	 * Sends headers and returns response contents
	 *
	 * @return string;
	 */
	function __toString() {
		if(headers_sent()) {
			return (string) $this->content;
		}

		header($this->protocol . ' ' . $this->status . ' ' . $this->statusTexts[$this->status], true, $this->status);

		foreach($this->headers as $header => $value) {
			header($header . ': ' . $value);
		}

		return (string) $this->content;
	}
}
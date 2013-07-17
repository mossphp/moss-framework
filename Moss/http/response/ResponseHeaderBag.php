<?php
namespace Moss\http\response;

/**
 * Response header bag
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
abstract class ResponseHeaderBag {

	private $headers = array();

	/**
	 * Returns true if response has defined header of given type
	 *
	 * @param string $header
	 *
	 * @return bool
	 */
	public function hasHeader($header) {
		if(!isset($this->headers[$header])) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves header by its type from response
	 *
	 * @param string $header
	 *
	 * @return null|string
	 */
	public function getHeader($header) {
		if(!isset($this->headers[$header])) {
			return null;
		}

		return $this->headers[$header];
	}

	/**
	 * Adds header to response
	 *
	 * @param string $header
	 * @param string $value
	 *
	 * @return Response|ResponseInterface
	 */
	public function addHeader($header, $value) {
		$this->headers[$header] = $value;

		return $this;
	}

	/**
	 * Retrieves all headers as array
	 *
	 * @return array
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * Overwrites all response headers
	 *
	 * @param $headers
	 *
	 * @return Response
	 */
	public function setHeaders($headers) {
		$this->headers = $headers;

		return $this;
	}

	/**
	 * Removes header from response
	 *
	 * @param string $header
	 *
	 * @return Response|ResponseInterface
	 */
	public function removeHeader($header) {
		if(isset($this->headers[$header])) {
			unset($this->headers[$header]);
		}

		return $this;
	}
}
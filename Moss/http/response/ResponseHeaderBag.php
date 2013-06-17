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
	 * @param string $headerType
	 *
	 * @return bool
	 */
	public function hasHeader($headerType) {
		if(!isset($this->headers[$headerType])) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves header by its type from response
	 *
	 * @param string $headerType
	 *
	 * @return null|string
	 */
	public function getHeader($headerType) {
		if(!isset($this->headers[$headerType])) {
			return null;
		}

		return $this->headers[$headerType];
	}

	/**
	 * Adds header to response
	 *
	 * @param string $header
	 *
	 * @return Response|ResponseInterface
	 */
	public function addHeader($header) {
		if(array_search($header, $this->headers) !== false) {
			return $this;
		}

		list($type, $value) = explode(':', $header, 2);
		$this->headers[$type] = trim($value);

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
		if(($key = array_search($header, $this->headers)) === false) {
			unset($this->headers[$key]);

			return $this;
		}

		list($type, $trash) = explode(':', $header, 2);
		if(isset($this->headers[$type])) {
			unset($this->headers[$type]);

			return $this;
		}

		return $this;
	}

}
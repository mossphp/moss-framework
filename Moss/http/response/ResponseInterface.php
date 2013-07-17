<?php
namespace Moss\http\response;

/**
 * Response interface
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface ResponseInterface {

	/**
	 * Returns true if response has defined header of given type
	 *
	 * @param string $headerType
	 *
	 * @return bool
	 */
	public function hasHeader($headerType);

	/**
	 * Retrieves header from response
	 *
	 * @param string $header
	 *
	 * @return null|string
	 */
	public function getHeader($header);

	/**
	 * Adds header to response
	 *
	 * @param string $header
	 * @param string $value
	 *
	 * @return ResponseInterface
	 */
	public function addHeader($header, $value);

	/**
	 * Retrieves all headers as array
	 *
	 * @return array
	 */
	public function getHeaders();

	/**
	 * Overwrites all response headers
	 *
	 * @param string $headers
	 *
	 * @return ResponseInterface
	 */
	public function setHeaders($headers);

	/**
	 * Removes header from response
	 *
	 * @param string $header
	 *
	 * @return ResponseInterface
	 */
	public function removeHeader($header);

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
	 * Casts response into string and sends headers
	 *
	 * @return string;
	 */
	public function __toString();
}

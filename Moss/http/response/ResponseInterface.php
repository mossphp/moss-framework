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
	 * Returns header value for given key
	 *
	 * @param string $header
	 * @param string $default
	 *
	 * @return null|string
	 */
	public function getHeader($header, $default = null);

	/**
	 * Sets header value
	 *
	 * @param string $header
	 * @param string $value
	 *
	 * @return $this
	 */
	public function setHeader($header, $value = null);

	/**
	 * Retrieves all headers as array
	 *
	 * @return array
	 */
	public function headers();

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

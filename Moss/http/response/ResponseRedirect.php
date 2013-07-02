<?php
namespace Moss\http\response;

use Moss\http\response\Response;

/**
 * Redirecting Response
 * Response redirects (Status 302) client to given address
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class ResponseRedirect extends Response {

	protected $delay;
	protected $address;

	/**
	 * Constructor
	 * Sets redirection address, delay and response content
	 *
	 * @param string $address redirection address
	 * @param int    $delay   redirection delay in seconds
	 */
	public function __construct($address, $delay = 0) {
		$this->address($address);
		$this->delay($delay);

		$this->content('Redirecting...');
		$this->status('302');
	}

	/**
	 * Sets redirection delay
	 *
	 * @param int $delay redirection delay in seconds
	 *
	 * @return ResponseRedirect
	 */
	public function delay($delay) {
		$this->delay = (int) $delay;
		return $this;
	}

	/**
	 * Sets redirection address
	 *
	 * @param string $address redirection address
	 *
	 * @return ResponseRedirect
	 */
	public function address($address) {
		$this->address = str_replace('&amp;', '&', $address);
		return $this;
	}

	/**
	 * Converts response content to string and sends headers
	 *
	 * @return string
	 * @throws \LengthException
	 */
	public function __toString() {
		if(headers_sent()) {
			return '<script type="text/javascript" language="javascript">setTimeout("window.location.href = \'' . $this->address . '\'", ' . ($this->delay * 1000) . ');</script>' . $this->content;
		}

		if($this->delay) {
			$this->addHeader('Refresh: ' . $this->delay . '; URL=' . $this->address);
		}
		else {
			$this->addHeader('Location: ' . $this->address);
		}


		return parent::__toString();
	}
}

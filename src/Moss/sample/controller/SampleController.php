<?php
namespace moss\sample\controller;

use moss\container\ContainerInterface;
use moss\http\response\Response;

class SampleController {

	/**
	 * Constructor, calls init function
	 *
	 * @param ContainerInterface $Container
	 */
	public function __construct(ContainerInterface $Container) {
		$this->Container = & $Container;
	}

	/**
	 * Method for initialisation operations
	 * Called at the end of constructor
	 */
	public function indexAction() {
		return new Response('Hello, this is sample controller. <a href="./autodoc">Go to documentation</a>');
	}
}
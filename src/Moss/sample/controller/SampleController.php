<?php
namespace Moss\sample\controller;

use Moss\container\ContainerInterface;
use Moss\http\response\Response;

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
		return new Response('Hello, this is sample controller');
	}
}
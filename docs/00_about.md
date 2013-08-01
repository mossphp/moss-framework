# General

## About

_**moss** - to relax or chill. the act of chilling_

Work with any framework should be easy, convinient and fast. That are main principies of _moss framework_.
Of course, `moss framework` wants to be fashionable and follows trends: `MVC`, `closures`, `events`, `dependency injection`, but only where it makes sense.

By default `moss framework` is a small framework, providing basic tools that can be used to build simple APIs or enterprise applications.
But when needed they can be easily replaced with components from other frameworks or independent libraries.

## Quickstart

### Instalation

Download from [github](https://github.com/Potfur/moss)
Install [composer](https://getcomposer.org/) and run `composer install` or `composer update` to install all required dependencies.
Since `moss framework` does not have any ORM yet, no further configuration is needed

### Controller

Create new controller `./src/quick/start/controller/QuickController.php` containing:

	<?php
	namespace quick\start\controller;

	use moss\container\ContainerInterface;
    use moss\http\response\Response;

	class QuickController {
		protected $Container;

		public function __construct($Container) {
			$this->Container = &$Container;
		}

		public function indexAction() {
			return new Response(__METHOD__);
		}
	}

Now when you call `http://127.0.0.1/moss/web/?controller=quick_start_Quick_index (assuming that framework is available under http://127.0.0.1/moss/web/), you should see method name `quick\start\controller\QuickController::indexAction`

### Routes

To register route to that action, in `./web/bootstrap.php` in section `router` add

	'index' => array(
	    'pattern' => '/index/',
	    'controller' => 'quick:start:Quick:index'
	)


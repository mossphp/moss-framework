# General

## About

_**Moss** - to relax or chill, the act of chilling_

`Moss` is a small, almost micro (but not another Sinatra wannabe) framework, providing basic tools for building simple web pages or APIs.

So what's the difference between other micro-frameworks?
`Moss` isn't some kind of cropped full stack framework, that was cut down to fit into _micro_ segment.
Neither one of those minimalistic, closure lovers :)

`Moss` was developed as a solution with small footprint, easily to extend, and least dependencies as possible (actually - none).

But still, `Moss framework` wants to be _fashionable_ and follows trends: `closures`, `event dispatching`, `dependency injection`

## Features

 * fully grown `Router` (not powerful but working :) ),
 * `Request` and `Response` objects (got http auth, and easy header management),
 * flash messages
 * dependency injection container
 * event dispatcher with `AOP`
 * closure and class controllers (that can be organized into bundles with fluent directory structure),
 * simple view that can be easily extended with bridge to use `Twig` (as package in composer)
 * and clean code
 * and more

## Quickstart

Download from [github](https://github.com/potfur/moss)

Create new controller `./src/Quick/Start/Controller/QuickController.php` containing:

	namespace Quick\Start\Controller;

	use Moss\Kernel\App;
    use Moss\Http\Response\Response;

	class DemoController
	{
		protected $app;

		public function __construct(App $container)
		{
			$this->app = $app;
		}

		public function indexAction()
		{
			return new Response(__METHOD__);
		}
	}

To register route to that action that allows to enter `http://127.0.0.1/moss/web/quick-start/`, in `./web/bootstrap.php` in section `router` add

	'index' => array(
	    'pattern' => '/quick-start/',
	    'controller' => '\Quick\Start\Demo::index'
	)

And that's it, the rest depends on your needs and skills.
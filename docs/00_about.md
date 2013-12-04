# General

## About

_**moss** - to relax or chill, the act of chilling_

Work with any framework should be easy, convenient and fast. That are main principles of `moss framework`.
Of course, `moss framework` wants to be fashionable and follows trends: `MVC`, `closures`, `event dispatching`, `dependency injection`, `aspect oriented programming`
But only where it makes sense.

By default `moss framework` is a small framework, providing basic tools that can be used to build simple web pages or APIs.
Its only dependency is [Twig](http://twig.sensiolabs.org).
But when needed they can be easily extended with components from other frameworks or independent libraries and grow into enterprise application.

## Quickstart

Download from [github](https://github.com/Potfur/moss)
Install [composer](https://getcomposer.org/) and run `composer install` or `composer update` to install all required dependencies.
Since `moss framework` does not have any ORM yet, no further configuration is needed

Create new controller `./src/quick/start/controller/QuickController.php` containing:

	namespace quick\start\controller;

	use moss\container\Container;
    use moss\http\response\Response;

	class QuickController
	{
		protected $container;

		public function __construct(Container $container)
		{
			$this->container = $container;
		}

		public function indexAction()
		{
			return new Response(__METHOD__);
		}
	}

Now when you call `http://127.0.0.1/moss/web/?controller=quick_start_quick_index` assuming that framework is available under http://127.0.0.1/moss/web/,
you should see method name `quick\start\controller\QuickController::indexAction`

To register route to that action that allows to enter `http://127.0.0.1/moss/web/quick-start/`, in `./web/bootstrap.php` in section `router` add

	'index' => array(
	    'pattern' => '/quick-start/',
	    'controller' => 'quick:start:quick:index'
	)

And that's it, the rest depends on your needs and skills.
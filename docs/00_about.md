# General

## About

_**Moss** - to relax or chill, the act of chilling_

Work with any framework should be easy, convenient and fast. That are main principles of `Moss framework`.
Of course, `Moss framework` wants to be fashionable and follows trends: `MVC`, `closures`, `event dispatching`, `dependency injection`, `aspect oriented programming`
But only where it makes sense.

By default `Moss framework` is a small framework, providing basic tools that can be used to build simple web pages or APIs.
Its only dependency is [Twig](http://twig.sensiolabs.org).
But when needed they can be easily extended with components from other frameworks or independent libraries and grow into enterprise application.

## Quickstart

Download from [github](https://github.com/Potfur/Moss)
Install [composer](https://getcomposer.org/) and run `composer install` or `composer update` to install all required dependencies.
Since `Moss framework` does not have any ORM yet, no further configuration is needed

Create new controller `./src/quick/start/controller/QuickController.php` containing:

	namespace quick\start\Controller;

	use Moss\Container\Container;
    use Moss\http\response\Response;

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

Now when you call `http://127.0.0.1/Moss/web/?controller=quick_start_quick_index` assuming that framework is available under http://127.0.0.1/Moss/web/,
you should see method name `quick\start\Controller\QuickController::indexAction`

To register route to that action that allows to enter `http://127.0.0.1/Moss/web/quick-start/`, in `./web/bootstrap.php` in section `router` add

	'index' => array(
	    'pattern' => '/quick-start/',
	    'controller' => 'quick:start:quick:index'
	)

And that's it, the rest depends on your needs and skills.
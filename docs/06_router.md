# Router

Routers responsibility is to translate incoming requests into controller calls with incoming query arguments.
 And the other way - from route name and arguments creates URL.

## Declaring route

First, declaring route pointing to controller `controller` and two arguments: required `foo` limited to digits and optional `bar` that accepts anything:

	$route = new \Moss\Http\Router\Route('/{foo:\d}/({bar})', 'controller');

Set argument default values, needed only for required arguments (eg. route /some-title/ should point to entry with id = 1:

    $route = new \Moss\Http\Router\Route('/some-title/)', 'controller', array('id' => 1));

### Controller

Controller can be defined in different ways.
As closure:

	$route = new \Moss\Http\Router\Route('/', function() { ... });

Or as something callable:

	function someController() { ... }
	$route = new \Moss\Http\Router\Route('/', 'someController'); // function name

	class SomeController {
		static public function someAction() { ... }
		public function otherAction() { ... }
		public function __invoke() { ... }
	}

	$route = new \Moss\Http\Router\Route('/', array('SomeController', 'someAction'); // static action
	$route = new \Moss\Http\Router\Route('/', array(new SomeController(), 'someAction'); // method of existing instance
	$route = new \Moss\Http\Router\Route('/', new SomeController(); // object as callable

Or as closure

	$route = new \Moss\Http\Router\Route('/', function() { ... });

Each of such defined controller will receive dependency container instance as first (and only) argument.

There is better way to define controllers, as classic _controller class_ with _action_.

	$route = new \Moss\Http\Router\Route('/{foo:\d}/({bar})', '\Some\Controller@some');

In this case, when route is called, controller instance will be created and dependency container will be injected to its constructor.
Actions do not receive any predefined arguments, to this is up to You.

### Limiting route - domain

Limited to domain:

	$route = new \Moss\Http\Router\Route('/some-title/)', 'controller');
    $route->host('foo.com');

Limited to sub domain:

	$route = new \Moss\Http\Router\Route('/some-title/)', 'controller');
	$route->host('sub.foo.com');

Or if we do not want to specify domain:

	$route = new \Moss\Http\Router\Route('/some-title/)', 'controller');
	$route->host('sub.{basename}');

### Limiting route - method

Limited methods:

	$route = new \Moss\Http\Router\Route('/some-title/)', 'controller');
    $route->methods(array('POST'));

Or in constructor:

	$route = new \Moss\Http\Router\Route('/some-title/)', 'controller', array('id' => 1), array('GET'));

**Method names are case insensitive**.

### Limiting route - schema

Limited schema:

    $route->schema('HTTP');

**Schema names are case insensitive**.

## Register route

Create router and register routes:

	$route = new \Moss\Http\Router\Route('/{foo:\w}/({bar:\d})/', $controller);

	$router = new Router();
	$router->register('routeName', $route);

## Match request to route

Match request - if matching route is found, controller from matching route is returned. Otherwise RouterException is thrown.

	$controller = $router->match(new Request());

## Generate URL from route

To generate URL

	$url = $router->make('routeName', array('foo' => 'foo'));

This will will return URL - if corresponding route exists, otherwise exception will be thrown

## View

In view use `url` function for generating urls

	<?php echo url('routeName', array('foo' => 'foo')) ?>

Twig bridge uses same function:

	{{ url('routeName', { 'foo': 'foo' }) }} // by route name


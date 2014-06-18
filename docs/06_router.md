# Router

Routers responsibility is to translate incoming requests into controller calls with incoming query arguments.
 And the other way - from route name and arguments creates URL.

## Declaring route

First, declaring route pointing to controller `controller` (where `controller` is function name) and two arguments: required `foo` limited to digits and optional `bar` that accepts anything:

	$route = new \Moss\Http\Router\Route('/{foo:\d}/({bar})', 'controller');

Or route with controller - action class (one with string name and second with proper callable)

	$route = new \Moss\Http\Router\Route('/{foo:\d}/({bar})', '\Some\Controller::some');
	$route = new \Moss\Http\Router\Route('/{foo:\d}/({bar})', array('\Some\Controller', 'some'));

Or create route with closure as controller

	$route = new \Moss\Http\Router\Route('/{foo:\w}/({bar:\d})', function() {
		return new \Moss\Response\Response('Hello world');
	});

Set argument default values, needed only for required arguments (eg. route /some-title/ should point to entry with id = 1:

    $route = new \Moss\Http\Router\Route('/some-title/)', '\Some\Controller::someAction', array('id' => 1));

### Limiting route - domain

Limited to domain:

	$route = new \Moss\Http\Router\Route('/some-title/)', '\Some\Controller::someAction');
    $route->host('foo.com');

Limited to sub domain:

	$route = new \Moss\Http\Router\Route('/some-title/)', '\Some\Controller::someAction');
	$route->host('sub.foo.com');

Or if we do not want to specify domain:

	$route = new \Moss\Http\Router\Route('/some-title/)', '\Some\Controller::someAction');
	$route->host('sub.{basename}');

### Limiting route - method

Limited methods:

	$route = new \Moss\Http\Router\Route('/some-title/)', '\Some\Controller::someAction');
    $route->methods(array('POST'));

Or in constructor:

	$route = new \Moss\Http\Router\Route('/some-title/)', '\Some\Controller::someAction', array('id' => 1), array('GET'));

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


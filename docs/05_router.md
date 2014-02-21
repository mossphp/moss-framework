# Router

Routers responsibility is to translate incoming URL requests into controller name (or controller itself) with query arguments.
 And the other way - from controller name (or route name), query arguments creates URI (there is difference between URL and URI).

## Declaring route

Create route ( pointing to controller `controller:action` and two query arguments: required `foo` and optional `bar` that accept anything:

	$route = new \Moss\Http\Rrouter\Route('/{foo}/({bar})/', 'controller:action');

Or create route with closure as controller

	$route = new \Moss\Http\Router\Route('/{foo:\w}/({bar:\d}/)', function() {
		return new \Moss\Response\Response('Hello world');
	});

Set argument requirements - accepted types - by default, values matching `[a-z0-9-._]` are accepted:

	$route = new \Moss\Http\Route\Route('/{foo}/({bar}/)', 'controller:action');
    $route->requirements(array('foo' => '\w+', 'bar' => '\d*'));

Same defined in constructor:

	$route = new \Moss\Http\Router\Route('/{foo:\w}/({bar:\d})/', 'controller:action');

Set argument default values, needed only for required arguments (eg. route /some-title/ should point to entry with id = 1:

    $route->arguments(array('id' => 1));

Limited to domain:

    $route->host('foo.bar.com');

Limited to sub domain:

	$route->host('foo.{basename}');

Limited methods:

    $route->methods(array('POST'));

Limited schema:

    $route->schema('HTTP');

## Register route

Create router and register routes:

	$route = new \Moss\Http\Router\Route('/{foo:\w}/({bar:\d})/', 'controller:action');

	$router = new Router();
	$router->register('routeName', $route);

## Match request to route

Match request - if matching route is found, controller from matching route is returned. Otherwise RouterException is thrown.

	$controller = $router->match(new Request);

## Generate URL from route

To generate url from controller name

	$url = $router->make('controller:action', array('foo' => 'foo')); // by controller, works only for non-closure routes
	$url = $router->make('routeName', array('foo' => 'foo')); // by route name

Both methods will return same url - if corresponding route exists.
If matching route does not exist, `Router` will return normal URL.

**All non-closure controllers can be accessed via normal URLs.**

## Twig bridge extension

To generate URL from route in `Twig` use:

	{{ Url('controller:action', { 'foo': 'foo' }) }} // by controller, works only for non-closure routes
	{{ Url('routeName', { 'foo': 'foo' }) }} // by route name


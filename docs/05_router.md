# Router

Routers responsibility is to translate incoming URL requests into controller name (or controller itself) with query arguments.
 And the other way - from controller name (or route name), query arguments creates URL.

## Declaring route

Create route ( pointing to controller `controller:action` and two query arguments: required `foo` and optional `bar` ):

	$Route = new \moss\router\Route('/{foo}/({bar})/', 'controller:action');

Or create route with closure as controller

	$Route = new \moss\router\Route('/{foo}/({bar})/', function() {
		return new \moss\response\Response('Hello world');
	});

Set argument requirements - accepted types - by default, all values are accepted:

    $Route->requirements(array('foo' => '\w+', 'bar' => '\d*'));

Set argument default values, needed only for required arguments:

    $Route->arguments(array('foo' => 'foo'));

If limitation is needed - set host, method and schema:

    $Route->host('foo.{basename}');
    $Route->methods(array('POST'));
    $Route->schema('HTTP');

## Register route

Create router and register routes:

	$Router = new Router();
	$Router->register('routeName', $Route);

## Match request to route

Match request - if matching route found, controller from matching route is returned. Otherwise RouterException is thrown.

	$controller = $Router->match(new Request);

## Generate URL from route

To generate url from controller name

	$url = $Router->make('controller:action', array('foo' => 'foo')); // by controller, works only for non-closure routes
	$url = $Router->make('routeName', array('foo' => 'foo')); // by route name

Both methods will return same url - if corresponding route exists.
If matching route does not exist, `Router` will return normal URL.

**All non-closure controllers can be accessed via normal URLs.**

## Twig bridge extension

To generate URL from rotue in `Twig` use:

	{{ Url('controller:action', { 'foo': 'foo' }) }} // by controller, works only for non-closure routes
	{{ Url('routeName', { 'foo': 'foo' }) }} // by route name


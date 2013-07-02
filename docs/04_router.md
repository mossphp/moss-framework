# Router

## Declaring route

Create route ( with controller `controller:action` and two arguments: required `foo` and optional `bar` ):

	$Route = new \Moss\router\Route('/{foo}/({bar})/', 'controller:action');

Or create route with closure as controller

	$Route = new \Moss\router\Route('/{foo}/({bar})/', function() {
		return new \Moss\response\Response('Hello world');
	});

Set argument requirements - accepted types - by default, all values are accepted:

    $Route->requirements(array('foo' => '\w+', 'bar' => '\d*'));

Set argument default values, needed only for required arguments:

    $Route->defaults(array('foo' => 'foo'));

If needed - set host, method and schema:

    $Route->host('foo.{basename}');
    $Route->methods(array('POST');
    $Route->schema('HTTP');

## Register route

Create router and register routes:

	$Router = new Router();
	$Router->register('routeName', $Route);

## Match request to route

Match request - if matching route found - controller is returned, otherwise RouterException is thrown.

	$controller = $Router->match(new Request);

## Generate URL from route

To generate url from controller name (when route not found, generates normal url):

	$url = $Router->make('controller:action', array('foo' => 'foo'));

Or by route name:

	$url = $Router->make('routeName', array('foo' => 'foo'));
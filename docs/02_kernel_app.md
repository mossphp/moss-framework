# App

`App` short for `Application` is the frameworks heart.
This the place where all defined components live and this is the thing that will be used to handle incoming requests.

Generally all configuration ie routes, components etc. will be read from configuration but sometime it is convenient to add it manually.
`Kernel` providers few methods for fast definitions:

 * `::route($name, $pattern, $controller, $arguments = array(), $methods = array())` - to define routes
 * `::component($name, $definition, $shared = false)` - defines component or property stored in container
 * `::listener($event, $definition, $priority = null)` - defines listener for event

And there is most important method `::run()` that will handle received request and return response (`ResponseInterface`).
So, handling request looks like this:

	$moss = new \Moss\Kernel\Kernel($config, $mode);
	$moss->run()->send();

Where `$config` is array containing your applications configuration and `$mode` is name of runtime mode (can be `null`).

In PHP >=5.4 it looks quite fancy

	(new \Moss\Kernel\Kernel($config, $mode))->run()->send();

## Components & methods

Also, App provides easy access to components:

 * `::container` - gives access to `\Moss\Container\Container` instance
 * `::config` - is for `\Moss\Config\Config`
 * `::router` - is `\Moss\Http\Router\Router` instance
 * `::dispatcher` - gives access to `\Moss\Dispatcher\Dispatcher`
 * `::session` - `\Moss\Http\Session\Session`
 * `::cookie` - `\Moss\Http\Cookie\Cookie`
 * `::request` - `\Moss\Http\Request\Request`

Every other component can be resolved trough `::get()` method or magically via `::__get()`.

	$request = $app->get('foo');
	$request = $app->foo;

There is also convenient method to fire events `::fire($event, $subject, $message)`:

	$result = $app->fire($event, new Comment(), 'Comment added');

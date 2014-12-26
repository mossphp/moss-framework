# App

`App` short for `Application` is the frameworks heart.
This the place where all defined components live and this is the thing that will be used to handle incoming requests.

Generally all configuration ie routes, components etc. will be read from configuration but sometime it is convenient to add it manually.
`App` providers few methods for fast definitions:

 * `::route($name, $pattern, $controller, $arguments = array(), $methods = array())` - to define routes
 * `::component($name, $definition, $shared = false)` - defines component or property stored in container
 * `::listener($event, $definition, $priority = null)` - defines listener for event

And there is most important method `::run()` that will handle received request and return response (`ResponseInterface`).
So, handling request looks like this:

	$moss = new \Moss\Kernel\App($config, $mode);
	$moss->run()->send();

Where `$config` is array containing your applications configuration and `$mode` is name of runtime mode (can be `null`).

In PHP >=5.4 it looks quite fancy

	(new \Moss\Kernel\App($config, $mode))->run()->send();

## Components & methods

Also, App provides easy access to components:

 * `::container()` - gives access to `\Moss\Container\ContainerInterface` instance
 * `::config()` - is for `\Moss\Config\ConfigInterface`
 * `::router()` - is `\Moss\Http\Router\RouterInterface` instance
 * `::dispatcher()` - gives access to `\Moss\Dispatcher\DispatcherInterface`
 * `::session()` - `\Moss\Http\Session\SessionInterface`
 * `::cookie()` - `\Moss\Http\Cookie\CookieInterface`
 * `::request()` - `\Moss\Http\Request\RequestInterface`

Every other component can be resolved trough `::get()` method.

	$request = $app->get('foo');

There is also convenient method to fire events `::fire($event, $subject, $message)`:

	$result = $app->fire($event, new Comment(), 'Comment added');

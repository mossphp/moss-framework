<?php
require __DIR__ . '/../Moss/config/ConfigInterface.php';
require __DIR__ . '/../Moss/config/Config.php';

require __DIR__ . '/../Moss/kernel/ErrorHandler.php';
require __DIR__ . '/../Moss/kernel/ExceptionHandler.php';

require __DIR__ . '/../Moss/loader/Loader.php';

// Bootstrap & config
require __DIR__ . '/bootstrap.php';

$Config = new \Moss\config\Config(isset($config) ? (array) $config : array());

// Error handling
$ErrorHandler = new \Moss\kernel\ErrorHandler($Config->get('kernel.error.level'));
$ErrorHandler->register();

$ExceptionHandler = new \Moss\kernel\ExceptionHandler($Config->get('kernel.error.detail'));
$ExceptionHandler->register();

// Loaders
$Loader = new \Moss\loader\Loader();
$Loader->registerNamespace('Moss', array('../'));
$Loader->registerNamespace(null, array('../src/'));
$Loader->register();

foreach((array) $Config->get('loaders.namespaces') as $namespace => $path) {
	$Loader->registerNamespace($namespace, $path);
}
foreach((array) $Config->get('loaders.prefixes') as $prefix => $path) {
	$Loader->registerPrefix($prefix, $path);
}
$Loader->register();
unset($namespace, $path, $prefix);

// Container
$Container = new \Moss\container\Container();
$defaults = array('arguments' => array(), 'methods' => array(), 'shared' => false);
foreach((array) $Config->get('container') as $name => $component) {
	$component = array_merge($defaults, $component);
	$Container->register($name, new \Moss\container\Component($component['class'], $component['arguments'], $component['methods']), $component['shared']);
}
unset($name, $component);

// Dispatcher
$Dispatcher = new \Moss\dispatcher\Dispatcher($Container);
$defaults = array('method' => null, 'arguments' => array());
foreach((array) $Config->get('dispatcher') as $event => $lArr) {
	foreach($lArr as $listener) {
		$listener = array_merge($defaults, $listener);
		$Dispatcher->register($event, new \Moss\dispatcher\Listener($listener['component'], $listener['method'], $listener['arguments']));
	}
}
unset($name, $event, $lArr, $listener);

// Router
$Router = new \Moss\router\Router();
$defaults = array('pattern' => null, 'controller' => null, 'requirements' => array());
foreach((array) $Config->get('router') as $name => $route) {
	$route = array_merge($defaults, $route);
	$Router->register($name, new \Moss\router\Route($route['pattern'], $route['controller'], $route['requirements']));
}
unset($name, $route);

$Session = new \Moss\http\session\Session($Config->get('kernel.session.agent'), $Config->get('kernel.session.ip'), $Config->get('kernel.session.host'), $Config->get('kernel.session.salt'));
$Cookie = new \Moss\http\cookie\Cookie($Config->get('kernel.cookie.domain'), $Config->get('kernel.cookie.path'), $Config->get('kernel.cookie.http'));
$Request = new \Moss\http\request\Request($Session, $Cookie);

$Container->instance('Config', $Config);
$Container->instance('Router', $Router);
$Container->instance('Dispatcher', $Dispatcher);
$Container->instance('Session', $Session);
$Container->instance('Cookie', $Cookie);
$Container->instance('Request', $Request);

// Kernel
$Core = new \Moss\kernel\Kernel($Container->get('Router'), $Container, $Container->get('Dispatcher'));
echo $Core->handle($Container->get('Request'));
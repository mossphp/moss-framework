<?php
require __DIR__ . '/../Moss/config/ConfigInterface.php';
require __DIR__ . '/../Moss/config/Config.php';

require __DIR__ . '/../Moss/kernel/ErrorHandler.php';
require __DIR__ . '/../Moss/kernel/ExceptionHandler.php';

require __DIR__ . '/../Moss/loader/Loader.php';

// Bootstrap & config
$bootstrap = (array) require __DIR__ . '/bootstrap.php';

$Config = new \Moss\config\Config(isset($bootstrap) ? (array) $bootstrap : array());

// Error handling
$ErrorHandler = new \Moss\kernel\ErrorHandler($Config->get('error.level'));
$ErrorHandler->register();

$ExceptionHandler = new \Moss\kernel\ExceptionHandler($Config->get('error.detail'));
$ExceptionHandler->register();

// Loaders
$Loader = new \Moss\loader\Loader();
$Loader->registerNamespace('Moss', array('../'));
$Loader->registerNamespace(null, array('../src/'));
$Loader->register();
if(!empty($bootstrap['loaders']['namespaces'])) {
	foreach((array) $bootstrap['loaders']['namespaces'] as $namespace => $path) {
		$Loader->registerNamespace($namespace, $path);
	}
}

if(!empty($bootstrap['loaders']['prefixes'])) {
	foreach((array) $bootstrap['loaders']['prefixes'] as $prefix => $path) {
		$Loader->registerPrefix($prefix, $path);
	}
}
$Loader->register();
unset($namespace, $path, $prefix);

// Container
$Container = new \Moss\container\Container();
if(!empty($bootstrap['container'])) {
	$defaults = array('arguments' => array(), 'methods' => array(), 'shared' => false);
	foreach((array) $bootstrap['container'] as $name => $component) {
		$component = array_merge($defaults, $component);
		$Container->register($name, new \Moss\container\Component($component['class'], $component['arguments'], $component['methods']), $component['shared']);
	}
	unset($name, $component);
}

// Dispatcher
$Dispatcher = new \Moss\dispatcher\Dispatcher($Container);
if(!empty($bootstrap['dispatcher'])) {
	$defaults = array('method' => null, 'arguments' => array());
	foreach((array) $bootstrap['dispatcher'] as $event => $lArr) {
		foreach($lArr as $listener) {
			$listener = array_merge($defaults, $listener);
			$Dispatcher->register($event, new \Moss\dispatcher\Listener($listener['component'], $listener['method'], $listener['arguments']));
		}
	}
	unset($name, $event, $lArr, $listener);
}

// Router
$Router = new \Moss\router\Router();
if(!empty($bootstrap['router'])) {
	$defaults = array('pattern' => null, 'controller' => null, 'requirements' => array());
	foreach((array) $bootstrap['router'] as $name => $route) {
		$route = array_merge($defaults, $route);
		$Router->register($name, new \Moss\router\Route($route['pattern'], $route['controller'], $route['requirements']));
	}
	unset($name, $route);
}

unset($bootstrap);

$Session = new \Moss\http\session\Session($Config->get('session.agent'), $Config->get('session.ip'), $Config->get('session.host'), $Config->get('session.salt'));
$Cookie = new \Moss\http\cookie\Cookie($Config->get('cookie.domain'), $Config->get('cookie.path'), $Config->get('cookie.http'));
$Request = new \Moss\http\request\Request($Session, $Cookie);

$Container->register('Config', $Config);
$Container->register('Router', $Router);
$Container->register('Dispatcher', $Dispatcher);
$Container->register('Session', $Session);
$Container->register('Cookie', $Cookie);
$Container->register('Request', $Request);

// Kernel
$Core = new \Moss\kernel\Kernel($Container->get('Router'), $Container, $Container->get('Dispatcher'));
echo $Core->handle($Container->get('Request'));
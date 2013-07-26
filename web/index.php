<?php
require __DIR__ . '/../Moss/config/ConfigInterface.php';
require __DIR__ . '/../Moss/config/Config.php';

require __DIR__ . '/../Moss/kernel/ErrorHandler.php';
require __DIR__ . '/../Moss/kernel/ExceptionHandler.php';

require __DIR__ . '/../Moss/loader/Loader.php';

// Bootstrap & config
$bootstrap = (array) require __DIR__ . '/bootstrap.php';
$Config = new \Moss\config\Config(isset($bootstrap) ? (array) $bootstrap : array());
unset($bootstrap);

// Error handling
$ErrorHandler = new \Moss\kernel\ErrorHandler($Config->get('framework.error.level'));
$ErrorHandler->register();

$ExceptionHandler = new \Moss\kernel\ExceptionHandler($Config->get('framework.error.detail'));
$ExceptionHandler->register();

// Loaders
$Loader = new \Moss\loader\Loader();
$Loader->addNamespace('Moss', array('../'));
$Loader->addNamespace(null, array('../src/'));
$Loader->addNamespaces($Config->get('namespaces'));

$composerAutoloadPath = __DIR__ . '/../vendor/composer/autoload_namespaces.php';
if(is_file($composerAutoloadPath)) {
	$Loader->addNamespaces((array) require $composerAutoloadPath);
}
unset($composerAutoloadPath);

$Loader->register();

// Container
$Container = new \Moss\container\Container();
foreach((array) $Config->get('container') as $name => $component) {
	if(isset($component['class'])) {
		$Container->register($name, new \Moss\container\Component($component['class'], $component['arguments'], $component['methods']), $component['shared']);
		continue;
	}

	$Container->register($name, $component);
}
unset($name, $component);

// Dispatcher
$Dispatcher = new \Moss\dispatcher\Dispatcher($Container);
foreach((array) $Config->get('dispatcher') as $event => $listeners) {
	foreach($listeners as $listener) {
		$Dispatcher->register($event, new \Moss\dispatcher\Listener($listener['component'], $listener['method'], $listener['arguments']));
	}
}
unset($event, $listeners, $listener);

// Router
$Router = new \Moss\router\Router();
foreach((array) $Config->get('router') as $name => $route) {
	$Router->register($name, new \Moss\router\Route($route['pattern'], $route['controller'], $route['requirements']));
}
unset($name, $route);

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
$Core->handle($Container->get('Request'))->send();
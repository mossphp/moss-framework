<?php
require __DIR__ . '/../moss/config/ConfigInterface.php';
require __DIR__ . '/../moss/config/Config.php';

require __DIR__ . '/../moss/kernel/ErrorHandler.php';
require __DIR__ . '/../moss/kernel/ExceptionHandler.php';

require __DIR__ . '/../moss/loader/Loader.php';

// Bootstrap & config
$bootstrap = (array) require __DIR__ . '/bootstrap.php';
$Config = new \moss\config\Config(isset($bootstrap) ? (array) $bootstrap : array());
unset($bootstrap);

// Error handling
$ErrorHandler = new \moss\kernel\ErrorHandler($Config->get('framework.error.level'));
$ErrorHandler->register();

$ExceptionHandler = new \moss\kernel\ExceptionHandler($Config->get('framework.error.detail'));
$ExceptionHandler->register();

// Loaders
$Loader = new \moss\loader\Loader();
$Loader->addNamespace('moss', array('../'));
$Loader->addNamespace(null, array('../src/'));
$Loader->addNamespaces($Config->get('namespaces'));

$composerAutoloadPath = __DIR__ . '/../vendor/composer/autoload_namespaces.php';
if(is_file($composerAutoloadPath)) {
	$Loader->addNamespaces((array) require $composerAutoloadPath);
}
unset($composerAutoloadPath);

$Loader->register();

// Container
$Container = new \moss\container\Container();
foreach((array) $Config->get('container') as $name => $component) {
	if(isset($component['class'])) {
		$Container->register($name, new \moss\container\Component($component['class'], $component['arguments'], $component['methods']), $component['shared']);
		continue;
	}

	if(isset($component['closure'])) {
		$Container->register($name, $component['closure'], isset($component['shared']));
		continue;
	}

	$Container->register($name, $component);
}
unset($name, $component);

// Dispatcher
$Dispatcher = new \moss\dispatcher\Dispatcher($Container);
foreach((array) $Config->get('dispatcher') as $event => $listeners) {
	foreach($listeners as $listener) {
		if(isset($listener['closure'])) {
			$Dispatcher->register($event, $listener['closure']);
			continue;
		}

		$Dispatcher->register($event, new \moss\dispatcher\Listener($listener['component'], $listener['method'], $listener['arguments']));
	}
}
unset($event, $listeners, $listener);

// Router
$Router = new \moss\router\Router();
foreach((array) $Config->get('router') as $name => $route) {
	$Router->register($name, new \moss\router\Route($route['pattern'], $route['controller'], $route['requirements']));
}
unset($name, $route);

$Session = new \moss\http\session\Session($Config->get('session.agent'), $Config->get('session.ip'), $Config->get('session.host'), $Config->get('session.salt'));
$Cookie = new \moss\http\cookie\Cookie($Config->get('cookie.domain'), $Config->get('cookie.path'), $Config->get('cookie.http'));
$Request = new \moss\http\request\Request($Session, $Cookie);

$Container->register('Config', $Config);
$Container->register('Router', $Router);
$Container->register('Dispatcher', $Dispatcher);
$Container->register('Session', $Session);
$Container->register('Cookie', $Cookie);
$Container->register('Request', $Request);

// Kernel
$Core = new \moss\kernel\Kernel($Container->get('Router'), $Container, $Container->get('Dispatcher'));
$Core->handle($Container->get('Request'))->send();
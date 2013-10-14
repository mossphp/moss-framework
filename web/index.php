<?php
const __ROOT__ = __DIR__;

require __ROOT__ . '/../moss/config/ConfigInterface.php';
require __ROOT__ . '/../moss/config/Config.php';

require __ROOT__ . '/../moss/kernel/ErrorHandler.php';
require __ROOT__ . '/../moss/kernel/ExceptionHandler.php';

require __ROOT__ . '/../moss/loader/Loader.php';

// Bootstrap & config
$Config = new \moss\config\Config();
$Config->import((array) require __ROOT__ . '/../bootstrap/bootstrap.php');

// Error handling
$ErrorHandler = new \moss\kernel\ErrorHandler($Config->get('framework.error.level'));
$ErrorHandler->register();

$ExceptionHandler = new \moss\kernel\ExceptionHandler($Config->get('framework.error.detail') && isset($_SERVER['REQUEST_METHOD']));
$ExceptionHandler->register();

// Loaders
$Loader = new \moss\loader\Loader();
$Loader->addNamespace('moss', array(__ROOT__ . '/../'));
$Loader->addNamespace(null, array(__ROOT__ . '/../src/'));
$Loader->addNamespaces($Config->get('namespaces'));

$composerAutoloadPath = __ROOT__ . '/../vendor/composer/autoload_namespaces.php';
if (is_file($composerAutoloadPath)) {
    $Loader->addNamespaces((array) require $composerAutoloadPath);
}
unset($composerAutoloadPath);
$Loader->register();

// Container
$Container = new \moss\container\Container();
foreach ((array) $Config->get('container') as $name => $component) {
    if (isset($component['class'])) {
        $Container->register($name, new \moss\container\Component($component['class'], $component['arguments'], $component['methods']), $component['shared']);
        continue;
    }

    if (isset($component['closure'])) {
        $Container->register($name, $component['closure'], isset($component['shared']));
        continue;
    }

    $Container->register($name, $component);
}
unset($name, $component);

// Dispatcher
$Dispatcher = new \moss\dispatcher\Dispatcher($Container);
foreach ((array) $Config->get('dispatcher') as $event => $listeners) {
    foreach ($listeners as $listener) {
        if (isset($listener['closure'])) {
            $Dispatcher->register($event, $listener['closure']);
            continue;
        }

        $Dispatcher->register($event, new \moss\dispatcher\Listener($listener['component'], $listener['method'], $listener['arguments']));
    }
}
unset($event, $listeners, $listener);

// Router
$Router = new \moss\router\Router();
foreach ((array) $Config->get('router') as $name => $route) {
    $Route = new \moss\router\Route($route['pattern'], $route['controller'], $route['arguments']);

    foreach ($route as $key => $value) {
        switch ($key) {
            case 'methods':
                $Route->methods($value);
                break;
            case 'host':
                $Route->host($value);
                break;
            case 'schema':
                $Route->schema($value);
                break;
        }
    }

    $Router->register($name, $Route);
}
unset($name, $route, $value);

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
$Core
    ->handle($Container->get('Request'))
    ->send();
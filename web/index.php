<?php
const __ROOT__ = __DIR__;

require __ROOT__ . '/../moss/config/ConfigInterface.php';
require __ROOT__ . '/../moss/config/Config.php';

require __ROOT__ . '/../moss/kernel/ErrorHandler.php';
require __ROOT__ . '/../moss/kernel/ExceptionHandler.php';

require __ROOT__ . '/../moss/loader/Loader.php';

use moss\config\Config;
use moss\kernel\ErrorHandler;
use moss\kernel\ExceptionHandler;
use moss\loader\Loader;

use moss\container\Container;
use moss\container\Component;

use moss\dispatcher\Dispatcher;
use moss\dispatcher\Listener;

use moss\http\router\Router;
use moss\http\router\Route;
use moss\http\request\Request;
use moss\http\session\Session;
use moss\http\cookie\Cookie;

use moss\kernel\Kernel;

// bootstrap & config
$config = new Config((array) require __ROOT__ . '/bootstrap.php');

// error handling
$errorHandler = new ErrorHandler($config->get('framework.error.level'));
$errorHandler->register();

$exceptionHandler = new ExceptionHandler($config->get('framework.error.detail') && isset($_SERVER['REQUEST_METHOD']));
$exceptionHandler->register();

// autoloader
$loader = new Loader();
$loader->addNamespace('moss', array(__ROOT__ . '/../moss/'));
$loader->addNamespace(null, array(__ROOT__ . '/../src/'));
$loader->addNamespaces($config->get('namespaces'));

$composerAutoloadPath = __ROOT__ . '/../vendor/composer/autoload_namespaces.php';
if (is_file($composerAutoloadPath)) {
    $loader->addNamespaces((array) require $composerAutoloadPath);
}
unset($composerAutoloadPath);
$loader->register();

// container
$container = new Container();
foreach ((array) $config->get('container') as $name => $component) {
    if (isset($component['class'])) {
        $container->register($name, new Component($component['class'], $component['arguments'], $component['methods']), $component['shared']);
        continue;
    }

    if (isset($component['closure'])) {
        $container->register($name, $component['closure'], isset($component['shared']));
        continue;
    }

    $container->register($name, $component);
}
unset($name, $component);

// dispatcher
$dispatcher = new Dispatcher($container);
foreach ((array) $config->get('dispatcher') as $event => $listeners) {
    foreach ($listeners as $listener) {
        if (isset($listener['closure'])) {
            $dispatcher->register($event, $listener['closure']);
            continue;
        }

        $dispatcher->register($event, new Listener($listener['component'], $listener['method'], $listener['arguments']));
    }
}
unset($event, $listeners, $listener);

// router
$router = new Router();
foreach ((array) $config->get('router') as $name => $definition) {
    $route = new Route($definition['pattern'], $definition['controller'], $definition['arguments']);

    foreach ($definition as $key => $value) {
        switch ($key) {
            case 'methods':
                $route->methods($value);
                break;
            case 'host':
                $route->host($value);
                break;
            case 'schema':
                $route->schema($value);
                break;
        }
    }

    $router->register($name, $route);
}
unset($name, $definition, $value);

// request
$session = new Session($config->get('framework.session.agent'), $config->get('framework.session.ip'), $config->get('framework.session.host'), $config->get('framework.session.salt'));
$cookie = new Cookie($config->get('framework.cookie.domain'), $config->get('framework.cookie.path'), $config->get('framework.cookie.http'));
$request = new Request($session, $cookie);

// registering components
$container->register('config', $config);
$container->register('router', $router);
$container->register('dispatcher', $dispatcher);
$container->register('session', $session);
$container->register('cookie', $cookie);
$container->register('request', $request);

// Kernel
$kernel = new Kernel($container->get('router'), $container, $container->get('dispatcher'));
$kernel
    ->handle($container->get('request'))
    ->send();

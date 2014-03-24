<?php
require __DIR__ . '/../Moss/Config/ConfigInterface.php';
require __DIR__ . '/../Moss/Config/ConfigException.php';
require __DIR__ . '/../Moss/Config/Config.php';

require __DIR__ . '/../Moss/Kernel/ErrorHandler.php';
require __DIR__ . '/../Moss/Kernel/ExceptionHandler.php';

require __DIR__ . '/../Moss/Loader/Loader.php';

use Moss\Config\Config;
use Moss\Kernel\ErrorHandler;
use Moss\Kernel\ExceptionHandler;
use Moss\Loader\Loader;

use Moss\Container\Container;
use Moss\Container\Component;

use Moss\Dispatcher\Dispatcher;
use Moss\Dispatcher\Listener;

use Moss\Http\Router\Router;
use Moss\Http\Router\Route;
use Moss\Http\Request\Request;
use Moss\Http\Session\Session;
use Moss\Http\Cookie\Cookie;

use Moss\Kernel\Kernel;

// bootstrap & config
$config = new Config((array) require __DIR__ . '/bootstrap.php');

// error handling
$errorHandler = new ErrorHandler($config->get('framework.error.level'));
$errorHandler->register();

$exceptionHandler = new ExceptionHandler($config->get('framework.error.detail') && isset($_SERVER['REQUEST_METHOD']));
$exceptionHandler->register();

// autoloader
$loader = new Loader();
$loader->addNamespace('Moss', array(__DIR__ . '/../Moss/'));
$loader->addNamespace(null, array(__DIR__ . '/../src/'));

$composerAutoloadPath = __DIR__ . '/../vendor/composer/autoload_namespaces.php';
if (is_file($composerAutoloadPath)) {
    $loader->addNamespaces((array) require $composerAutoloadPath);
}
unset($composerAutoloadPath);
$loader->register();

// container
$container = new Container();
foreach ((array) $config->get('container') as $name => $component) {
    if (array_key_exists('component', $component) && is_callable($component['component'])) {
        $container->register($name, $component['component'], $component['shared']);
        continue;
    }

    $container->register($name, $component);
}
unset($name, $component);

// dispatcher
$dispatcher = new Dispatcher($container);
foreach ((array) $config->get('dispatcher') as $event => $listeners) {
    foreach ($listeners as $listener) {
        $dispatcher->register($event, $listener);
    }
}
unset($event, $listeners, $listener);

// router
$router = new Router();
foreach ((array) $config->get('router') as $name => $definition) {
    $route = new Route(
        $definition['pattern'],
        $definition['controller'],
        $definition['arguments'],
        $definition['methods']
    );

    if (array_key_exists('host', $definition)) {
        $route->host($value);
    }
    if (array_key_exists('schema', $definition)) {
        $route->schema($value);
    }

    $router->register($name, $route);
}
unset($name, $definition, $value);

// request
$session = new Session($config->get('framework.session.name'), $config->get('framework.session.cacheLimiter'));
$cookie = new Cookie($config->get('framework.cookie.domain'), $config->get('framework.cookie.path'));
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

<?php
error_reporting(-1);
ini_set( 'display_errors', true);

const __ROOT__ = __DIR__;

require __ROOT__ . '/../moss/config/ConfigInterface.php';
require __ROOT__ . '/../moss/config/Config.php';

require __ROOT__ . '/../moss/kernel/ErrorHandler.php';
require __ROOT__ . '/../moss/kernel/ExceptionHandler.php';

require __ROOT__ . '/../moss/loader/Loader.php';

// bootstrap & config
$config = new \moss\config\Config();
$config->import((array) require __ROOT__ . '/bootstrap.php');

// error handling
$errorHandler = new \moss\kernel\ErrorHandler($config->get('framework.error.level'));
$errorHandler->register();

$exceptionHandler = new \moss\kernel\ExceptionHandler($config->get('framework.error.detail') && isset($_SERVER['REQUEST_METHOD']));
$exceptionHandler->register();

$loader = new \moss\loader\Loader();
$loader->addNamespace('moss', array(__ROOT__ . '/../'));
$loader->addNamespace(null, array(__ROOT__ . '/../src/'));
$loader->addNamespaces($config->get('namespaces'));

$composerAutoloadPath = __ROOT__ . '/../vendor/composer/autoload_namespaces.php';
if (is_file($composerAutoloadPath)) {
    $loader->addNamespaces((array) require $composerAutoloadPath);
}
unset($composerAutoloadPath);
$loader->register();

// container
$container = new \moss\container\Container();
foreach ((array) $config->get('container') as $name => $component) {
    if (isset($component['class'])) {
        $container->register($name, new \moss\container\Component($component['class'], $component['arguments'], $component['methods']), $component['shared']);
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
$dispatcher = new \moss\dispatcher\Dispatcher($container);
foreach ((array) $config->get('dispatcher') as $event => $listeners) {
    foreach ($listeners as $listener) {
        if (isset($listener['closure'])) {
            $dispatcher->register($event, $listener['closure']);
            continue;
        }

        $dispatcher->register($event, new \moss\dispatcher\Listener($listener['component'], $listener['method'], $listener['arguments']));
    }
}
unset($event, $listeners, $listener);

// router
$router = new \moss\router\Router();
foreach ((array) $config->get('router') as $name => $definition) {
    $route = new \moss\router\Route($definition['pattern'], $definition['controller'], $definition['arguments']);

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
$session = new \moss\http\session\Session($config->get('framework.session.agent'), $config->get('framework.session.ip'), $config->get('framework.session.host'), $config->get('framework.session.salt'));

$cookie = new \moss\http\cookie\Cookie($config->get('framework.cookie.domain'), $config->get('framework.cookie.path'), $config->get('framework.cookie.http'));

$request = new \moss\http\request\Request($session, $cookie);

$container->register('config', $config);
$container->register('router', $router);
$container->register('dispatcher', $dispatcher);
$container->register('session', $session);
$container->register('cookie', $cookie);
$container->register('uploader', $uploader);
$container->register('request', $request);

// Kernel
$Kernel = new \moss\kernel\Kernel($container->get('router'), $container, $container->get('dispatcher'));
$Kernel
    ->handle($container->get('request'))
    ->send();
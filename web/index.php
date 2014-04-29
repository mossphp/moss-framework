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

// autoloader
$loader = new Loader();
$loader->addNamespace('Moss', array(__DIR__ . '/../Moss/'));
$loader->addNamespace(null, array(__DIR__ . '/../src/'));
$loader->register();

$moss = new \Moss\Moss(require __DIR__ . '/bootstrap.php');
$moss->run()->send();

<?php
return array(
    'framework' => array(
        'error' => array(
            'display' => true,
            'level' => E_ALL | E_NOTICE,
            'detail' => true
        ),
        'session' => array(
            'name' => 'PHPSESSID',
            'cacheLimiter' => ''
        ),
        'cookie' => array(
            'domain' => null,
            'path' => '/',
            'http' => true,
            'ttl' => 2592000 // one month
        )
    ),
    'namespaces' => array(),
    'container' => array(
        'paths' => array(
            'app' => __DIR__ . '/../src/',
            'base' => __DIR__ . '/../',
            'cache' => __DIR__ . '/../cache/',
            'compile' => __DIR__ . '/../compile/',
            'public' => __DIR__ . '/../web/',
        ),
        'view' => array(
            'closure' => function (\Moss\Container\Container $container) {
                    $view = new \Moss\View\View();
                    $view
                        ->set('request', $container->get('request'))
                        ->set('config', $container->get('config'));

                    $router = $container->get('router');
                    $view['url'] = function ($identifier = null, $arguments = array()) use ($router) {
                        return $router->make($identifier, $arguments);
                    };

                    return $view;
                }
        ),
    ),
    'dispatcher' => array(
        'kernel.request' => array(),
        'kernel.route' => array(),
        'kernel.controller' => array(),
        'kernel.response' => array(),
        'kernel.send' => array(),
        'kernel.403' => array(),
        'kernel.404' => array(),
        'kernel.500' => array()
    ),
    'router' => array(),
    'import' => array(
        (array) require __ROOT__ . '/../src/Moss/Sample/bootstrap.php'
    )
);
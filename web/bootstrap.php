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
        'logger' => array(
            'closure' => function () {
                    return new \moss\logger\Logger('../log/log.txt', false);
                },
            'shared' => true,
        ),
        'view' => array(
            'closure' => function (\moss\container\Container $container) {
                    $options = array(
                        'debug' => true,
                        'auto_reload' => true,
                        'strict_variables' => false,
                        'cache' => '../compile/'
                    );

                    $Twig = new Twig_Environment(new Twig_Bridge_Loader_File(), $options);
                    $Twig->setExtensions(
                         array(
                              new Twig_Bridge_Extension_Resource(),
                              new Twig_Bridge_Extension_Url($container->get('router')),
                              new Twig_Bridge_Extension_Trans(),
                              new Twig_Extensions_Extension_Text(),
                         )
                    );

                    $View = new \moss\view\View($Twig);
                    $View
                        ->set('request', $container->get('request'))
                        ->set('config', $container->get('config'));

                    return $View;
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
    'router' => array(
    ),
    'import' => array(
        (array) require __ROOT__ . '/../src/moss/sample/bootstrap.php'
    )
);
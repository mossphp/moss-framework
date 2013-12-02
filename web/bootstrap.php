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
        ),
        'uploader' => array(
            'dir' => array(
                'path' => __ROOT__ . '/upload/',
                'depth' => 2,
                'length' => 2
            )
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
            'closure' => function (\moss\container\Container $Container) {
                    $options = array(
                        'debug' => true,
                        'auto_reload' => true,
                        'strict_variables' => false,
                        'cache' => '../compile/'
                    );

                    $Twig = new Twig_Environment(new Twig_Bridge_Loader_Bridge(), $options);
                    $Twig->setExtensions(
                         array(
                              new Twig_Bridge_Extension_Resource(),
                              new Twig_Bridge_Extension_Url($Container->get('Router')),
                              new Twig_Bridge_Extension_Trans(),
                              new Twig_Extensions_Extension_Text(),
                         )
                    );

                    $View = new \moss\view\View($Twig);
                    $View
                        ->set('request', $Container->get('Request'))
                        ->set('config', $Container->get('Config'));

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
        'main' => array(
            'pattern' => '/',
            'controller' => 'moss:sample:sample:index',
            'arguments' => array(),
            'host' => null,
            'schema' => null,
            'methods' => array()
        ),
        'autodoc' => array(
            'pattern' => '/source/',
            'controller' => 'moss:sample:sample:source',
        )
    )
);
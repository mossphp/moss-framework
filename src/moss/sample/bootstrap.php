<?php
return array(
    'container' => array(
        'flash' => array(
            'closure' => function (\moss\container\ContainerInterface $container) {
                    return new \moss\http\session\FlashBag($container->get('session'));
                },
            'shared' => true,
        ),
        'security' => array(
            'closure' => function (\moss\container\ContainerInterface $container) {
                    $stash = new \moss\security\TokenStash($container->get('session'));

                    // uri to login action
                    $url = $container
                        ->get('router')
                        ->make('moss:sample:Sample:login');

                    $security = new \moss\security\Security($stash, $url);

                    // protects all actions but index and login
                    $security->registerArea(new \moss\security\Area('*:*:*:!index|login|auth'));

                    // registers fake provider
                    $security->registerUserProvider(new \moss\sample\provider\UserProvider());

                    return $security;
                },
            'shared' => true
        )
    ),
    'dispatcher' => array(
        'kernel.route' => array(
            array(
                'closure' => function (\moss\container\ContainerInterface $container) {
                        // tries to authenticate and authorize user
                        $request = $container->get('request');
                        $container->get('security')
                                  ->authenticate($request)
                                  ->authorize($request);
                    }
            )
        ),
        'kernel.route:exception' => array(
            array(
                'closure' => function (\moss\container\ContainerInterface $container) {
                        // if authorization or authentication fails this will redirect to login form
                        $url = $container->get('security')
                                         ->loginUrl();

                        $response = new \moss\http\response\ResponseRedirect($url, 2);
                        $response->status(403);
                        $response->content('Forbidden, you will be redirected... (this is an event action)');

                        return $response;
                    }
            )
        ),
    ),
    'router' => array(
        'main' => array(
            'pattern' => '/',
            'controller' => 'moss:sample:Sample:index',
            'arguments' => array(),
            'host' => null,
            'schema' => null,
            'methods' => array()
        ),
        'login' => array(
            'pattern' => '/login/',
            'controller' => 'moss:sample:Sample:login',
            'methods' => 'GET'
        ),
        'auth' => array(
            'pattern' => '/login/',
            'controller' => 'moss:sample:Sample:auth',
            'methods' => 'POST'
        ),
        'logout' => array(
            'pattern' => '/logout/',
            'controller' => 'moss:sample:Sample:logout',
        ),
        'source' => array(
            'pattern' => '/source/',
            'controller' => 'moss:sample:Sample:source',
        )
    )
);
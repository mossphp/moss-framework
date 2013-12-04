<?php
return array(
    'container' => array(
        'security' => array(
            'closure' => function (\moss\container\ContainerInterface $container) {
                    $stash = new \moss\security\TokenStash($container->get('session'));

                    // uri to login action
                    $url = $container
                        ->get('router')
                        ->make('moss:sample:sample:login');

                    $security = new \moss\security\Security($stash, $url);

                    // protects all actions but index and login
                    $security->registerArea(new \moss\security\Area('*:*:*:!index|login'));

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
                        // if authorization or authentication failed this will redirect to login form
                        $url = $container->get('security')
                                         ->loginUrl();

                        $response = new \moss\http\response\ResponseRedirect($url, 2);
                        $response->content('Forbidden, you will be redirected... (event action)');

                        return $response;
                    }
            )
        ),
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
        'login' => array(
            'pattern' => '/login/',
            'controller' => 'moss:sample:sample:login',
        ),
        'logout' => array(
            'pattern' => '/logout/',
            'controller' => 'moss:sample:sample:logout',
        ),
        'source' => array(
            'pattern' => '/source/',
            'controller' => 'moss:sample:sample:source',
        )
    )
);
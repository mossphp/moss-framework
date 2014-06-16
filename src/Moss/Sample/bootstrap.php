<?php
return array(
    'container' => array(
        'flash' => array(
            'component' => function (\Moss\Container\ContainerInterface $container) {
                    return new \Moss\Http\Session\FlashBag($container->get('session'));
                },
            'shared' => true,
        ),
        'security' => array(
            'component' => function (\Moss\Container\ContainerInterface $container) {
                    $stash = new \Moss\Security\TokenStash($container->get('session'));

                    // uri to login action
                    $url = $container
                        ->get('router')
                        ->make('login');

                    $security = new \Moss\Security\Security($stash, $url);

                    // protects all actions but index and login
                    $security->registerArea(new \Moss\Security\Area('/source'));

                    // registers fake provider
                    $security->registerUserProvider(new \Moss\Sample\Provider\UserProvider());

                    return $security;
                },
            'shared' => true
        ),
        'view' => array(
            'component' => function (\Moss\Container\ContainerInterface $container) {
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
        )
        /* View definition for Twig bridge
        'view' => array(
            'component' => function (Moss\Container\Container $container) {
                    $options = array(
                        'debug' => true,
                        'auto_reload' => true,
                        'strict_variables' => false,
                        'cache' => $container('path.compile')
                    );

                    $twig = new Twig_Environment(new Moss\Bridge\Loader\File(), $options);
                    $twig->setExtensions(
                        array(
                            new Moss\Bridge\Extension\Resource(),
                            new Moss\Bridge\Extension\Url($container->get('router')),
                            new Moss\Bridge\Extension\Trans(),
                            new Twig_Extensions_Extension_Text(),
                        )
                    );

                    $view = new \Moss\Bridge\View\View($twig);
                    $view
                        ->set('request', $container->get('request'))
                        ->set('config', $container->get('config'));

                    return $view;
                }
        )
        */
    ),
    'dispatcher' => array(
        'kernel.route' => array(
            function (\Moss\Container\ContainerInterface $container) {
                // tries to authenticate and authorize user
                $request = $container->get('request');
                $container->get('security')
                    ->authenticate($request)
                    ->authorize($request);
            }

        ),
        'kernel.route:exception' => array(
            function (\Moss\Container\ContainerInterface $container) {
                // if authorization or authentication fails this will redirect to login form
                $url = $container->get('security')
                    ->loginUrl();

                $response = new \Moss\Http\Response\ResponseRedirect($url, 5);
                $response->status(403);
                $response->content(
                    sprintf(
                        'Forbidden, you will be redirected within %u seconds... (this is an event defined in %s)',
                        $response->delay(),
                        __FILE__
                    )
                );

                return $response;
            }

        ),
    ),
    'router' => array(
        'main' => array(
            'pattern' => '/',
            'controller' => array('Moss\Sample\Controller\SampleController', 'index'),
        ),
        'login' => array(
            'pattern' => '/login/',
            'controller' => array('Moss\Sample\Controller\SampleController', 'login'),
            'methods' => 'GET'
        ),
        'auth' => array(
            'pattern' => '/login/',
            'controller' => array('Moss\Sample\Controller\SampleController', 'auth'),
            'methods' => 'POST'
        ),
        'logout' => array(
            'pattern' => '/logout/',
            'controller' => array('Moss\Sample\Controller\SampleController', 'logout'),
        ),
        'source' => array(
            'pattern' => '/source/',
            'controller' => array('Moss\Sample\Controller\SampleController', 'source'),
        )
    )
);
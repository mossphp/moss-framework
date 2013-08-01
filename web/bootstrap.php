<?php
return array(
	'framework' => array(
		'error' => array(
			'level' => E_ALL | E_NOTICE,
			'detail' => true
		),
		'session' => array(
			'agent' => true,
			'ip' => true,
			'salt' => 'RandomSaltString'
		),
		'cookie' => array(
			'domain' => null,
			'path' => '/',
			'http' => true,
		)
	),
	'namespaces' => array(
		'moss' => array(
			'../vendor/moss/lib/'
		)
	),
	'container' => array(
		'Logger' => array(
			'closure' => function () {
				return new \moss\logger\Logger('../log/log.txt', false);
			},
			'shared' => true,
		),
		'View' => array(
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
					     new Twig_Bridge_Extension_Locale(),
					     new Twig_Extensions_Extension_Text(),
					)
				);

				return new \moss\view\View(
					$Container->get('Request'),
					$Container->get('Config'),
					$Twig
				);
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
			'controller' => 'moss:sample:Sample:index',
			'requirements' => array(),
			'defaults' => array(),
			'arguments' => array(),
			'host' => null,
			'schema' => null,
			'methods' => array()
		),
		'autodoc' => array(
			'pattern' => '/autodoc/',
			'controller' => 'moss:autodoc:Autodoc:index',
		)
	)
);
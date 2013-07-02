<?php
return array(
	'config' => array(
		'error' => array(
			'level' => E_ALL | E_NOTICE,
			'detail' => true
		),
		'session' => array(
			'agent' => true,
			'ip' => true,
			'host' => true,
			'salt' => null
		),
		'cookie' => array(
			'domain' => null,
			'path' => '/',
			'http' => true,
		)
	),
	'loaders' => array(
		'namespaces' => array(),
		'prefixes' => array(
			'Twig' => array(
				'../vendor/twig/twig/lib/',
				'../vendor/twig/extensions/lib/',
				'../vendor/twig/bridge/lib/'
			)
		)
	),
	'container' => array(
		'Logger' => array(
			'class' => '\Moss\logger\Logger',
			'shared' => true,
			'arguments' => array(
				'../log/log.txt',
				false
			)
		),

		'View' => array(
			'class' => '\Moss\view\View',
			'arguments' => array(
				'@Request',
				'@Config',
				'@Twig'
			)
		),
		'Twig' => array(
			'class' => 'Twig_Environment',
			'arguments' => array(
				'@Twig_Bridge_Loader_Bridge',
				array(
					'debug' => true,
					'auto_reload' => true,
					'strict_variables' => false,
					'cache' => '../compile/'
				)
			),
			'methods' => array(
				'setExtensions' => array(array('@Twig_Bridge_Extension_Resource', '@Twig_Bridge_Extension_Url', '@Twig_Bridge_Extension_Locale', '@Twig_Extensions_Extension_Text'))
			)
		),
		'Twig_Bridge_Loader_Bridge' => array(
			'class' => 'Twig_Bridge_Loader_Bridge'
		),
		'Twig_Bridge_Extension_Resource' => array(
			'class' => 'Twig_Bridge_Extension_Resource'
		),
		'Twig_Bridge_Extension_Url' => array(
			'class' => 'Twig_Bridge_Extension_Url',
			'arguments' => array(
				'@Router'
			),
		),
		'Twig_Bridge_Extension_Locale' => array(
			'class' => 'Twig_Bridge_Extension_Locale'
		),
		'Twig_Extensions_Extension_Text' => array(
			'class' => 'Twig_Extensions_Extension_Text'
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
			'controller' => 'Moss:sample:Sample:index',
			'requirements' => array(),
			'defaults' => array(),
			'arguments' => array(),
			'host' => null,
			'schema' => null,
			'methods' => array()
		),
		'autodoc' => array(
			'pattern' => '/autodoc/',
			'controller' => 'Moss:autodoc:Autodoc:index',
		)
	)
);
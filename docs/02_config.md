# Config

`Config` object contains frameworks configuration, from error handling options, through components to route definitions.

## Configuration array / bootstrap

Entire configuration is read from plain php arrays, just call `::read($arr)` and pass array containing data to add, update existing configuration.
Configuration array must be properly formated, otherwise `ConfigException` will be thrown.

Configuration is split into sections:

	$arr = array(
		'framework' => array(),
		'namespaces' => array(),
		'container' => array(),
		'dispatcher' => array(),
		'router' => array()
	);

To access section:

	$Config = new Config(array('section' => array()));
	$section = $Config->get('section');

To access specific variable in section:

	$Config = new Config(array('section' => array('some' => array('var' => 'var'))));
	$var = $Config->get('section.some.var');

## Framework

There are two properties determining error reporting:

  * `framework.error.level` - reported error level, just like in `error_reporting()`;
  * `framework.error.detail` -if set to false will output only error message, if true - exception handler with verbose display will be used

Configuration of frameworks session and cookie wrappers is stored in `framework.session` and `framework.cookie` properties.

  * `framework.session.agent` - if set to `true`, auth key will contain user agents information
  * `framework.session.ip` - if `true`, auth key will contain users IP address (or his proxy)
  * `framework.session.salt` - salt, a random string that will be added to above

  * `framework.cookie.domain` - domain that cookie is available to, if set to empty string uses current domain
  * `framework.cookie.path` -path in which the cookie will be available on, by default set to '/' - cookie is available within the entire domain
  * `framework.cookie.http` - when `true` the cookie will be made accessible only through the HTTP protocol

In configuration array, this section looks like this:

	$arr = array(
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
		)
	);

## Namespaces

The `namespace` section contains list of namespaces that will be registered in autoloaders.
List is represented as associative array, where key is namespace name, ant value array of paths:

	$arr = array(
		'namespaces' => array(
			'\some\namespace\' => array(
				'\first\path\to\',
				'\second\path\to\'
			)
		)
	);

## Container

Component defintions can be found in `container` section.
Minimal definition, other array elements are optional

	$arr = array(
		'container' => array(
			'ComponentName' => array( // as array
				'class' => 'NamespacedComponentClass'
			),
			'OtherComponentName' => array( // as closure
	            'closure' => function(\moss\container\Container $Container) {
	                return new \stdClass();
	            }
	        )
		)
	);

Full definition can look like this:

	$arr = array(
		'container' => array(
			'ComponentName' => array(
				'class' => 'NamespacedComponentClass',
				'arguments' => array(
					'@OtherComponentName',
					'argument1',
					array(
						'argument'
						'array'
					)
				),
				'methods' => array(
					'firstMethod' => array(
		                '@OtherComponentName',
		                'argument1',
						array(
							'argument'
							'array'
						)
		            ),
		            'secondMethod' => array(
		                '@OtherComponentName',
		                'argument1',
		                array(
		                    'argument'
		                    'array'
		                )
		            )
				)
			)
		)
	);

Array under `arguments` key contains arguments passed to components constructor.
The `methods` associattive array, represents all methods that will be called after components initialization in set order.
For detailed description of constructor and method arguments go to `Container` chapter.

## Dispatcher & event listeners

The `dispatcher` section, holds list of all internal frameworks events.
Each event can have many listeners:

	$arr = array(
		'dispatcher' => array(
			'event.name' => array(
				array( // as array
					'component' => 'ComponentNameFromContainer',
					'method' => 'methodName', // optional
					'arguments' => array(
						'array',
						'containing'
						'method'
						'arguments'
					) // optional
				)
				array( // as closure
                    'closure' => function(\moss\container\Container $Container) {
                        return new \stdClass();
                    }
                )
			),
		)
	);

With AOP:

	$arr = array(
		'dispatcher' => array(
			'event.name:before' => array(array('component' => 'ComponentDefinitionSameAsAbove')),
			'event.name' => array(array('component' => 'ComponentDefinitionSameAsAbove')),
			'event.name:after' => array(array('component' => 'ComponentDefinitionSameAsAbove')),
			'event.name:exception' => array(array('component' => 'ComponentDefinitionSameAsAbove')),
		)
	)

For detailed description of method and method arguments go to `Dispatcher` chapter.

## Route definitions

All routes are defined in `router` section. Its an array, where keys are route names, and their values contain route properties.
The simples route definition looks like this:

	$arr = array(
		'router' => array(
			'routeName' => array( // for controller class
			    'pattern' => '/{foo}/({bar})/',
			    'controller' => 'Moss:sample:Sample:index',
			),
			'otherRoute' => function() { // for closure
				return new \moss\http\response\Response('Closure');
			}
		)
	);

Full route definition

	$arr = array(
		'router' => array(
			'routeName' => array(
			    'pattern' => '/{foo}/({bar})/',
			    'controller' => 'Moss:sample:Sample:index',
			    'requirements' => array(
					'foo' => '\w+',
					'bar' => '\w*'
			    ),
			    'defaults' => array(
					'foo' => 'foo'
			    ),
			    'arguments' => array(
					'locale' => 'pl',
					'format' => 'json'
			    ),
			    'host' => null,
			    'schema' => null,
			    'methods' => array('GET', 'POST')
			)
		)
	);

For detailed description of route definitions go to `Router` chapter.

## Twig bridge extension

`Config` instance is available in `Twig` templates (if used via `View` component), as `Config` variable.
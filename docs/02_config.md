# Config

`Config` object contains frameworks configuration, from error handling options, through components to route definitions.

## Configuration array / bootstrap

Entire configuration is read from plain php arrays, just call `::read($arr)` and pass array containing data to add to or update existing configuration.
Configuration array must have appropriate structure, otherwise `ConfigException` will be thrown.

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

from PHP > 5.4 can also:

	$Config = new Config(array('section' => array('some' => array('var' => 'var'))));
    $var = $Config->get('section')['some']['var'];

## Framework

There are two properties determining error reporting:

  * `framework.error.display` - if set to true will display errors - sets `ini_set('display_errors', true)`;
  * `framework.error.level` - reported error level sets `error_reporting()` to corresponding level;
  * `framework.error.detail` - if set to false will output only error message, if true - exception handler with verbose display will be used

Configuration of frameworks session and cookie wrappers is stored in `framework.session` and `framework.cookie` properties.

  * `framework.session.name` - session name, by default its `PHPSESSID`
  * `framework.session.cacheLimiter` - The cache limiter defines which cache control HTTP headers are sent to the client, by default its `''` - and turns off cache headers entirely

  * `framework.cookie.domain` - domain that cookie is available to, if set to empty string uses current domain
  * `framework.cookie.path` -path in which the cookie will be available on, by default set to '/' - cookie is available within the entire domain
  * `framework.cookie.http` - when `true` the cookie will be made accessible only through the HTTP protocol
  * `framework.cookie.ttl` - cookie Time To Live in seconds, by default its one month

In configuration array, this section looks like this:

	$arr = array(
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
				'ttl' => 2592000
			)
		)
	);

## Namespaces

The `namespace` section contains list of namespaces that will be registered in autoloaders.
List is represented as associative array, where key is namespace name, and value array of paths:

	$arr = array(
		'namespaces' => array(
			'\some\namespace\' => array(
				'\first\path\to\',
				'\second\path\to\'
			)
		)
	);

## Container

Component definitions can be found in `container` section.
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

Mainly components will be defined as closures, but for full picture entire definition can look like this:

	$arr = array(
		'container' => array(
			'ComponentName' => array(
				'class' => '\Namespaced\Component\Class',
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
The `methods` associative array, represents all methods that will be called after components initialization in set order.
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

As listener supports form of aspect oriented programming, each event is internally split into:

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
			    'pattern' => '/{foo:\w}/({bar:\d})/',
			    'controller' => 'moss:sample:Sample:index',
			),
			'otherRoute' => array( // for closure
				'pattern' => 'yadayda',
				'controller' => function() {
					return new \moss\http\response\Response('Closure');
				}
			)
		)
	);

Full route definition with sample values

	$arr = array(
		'router' => array(
			'routeName' => array(
			    'pattern' => '/{foo:\w}/({bar:\d})/',
			    'controller' => 'moss:sample:Sample:index',
			    'arguments' => array(
					'locale' => 'en',
					'format' => 'json'
			    ),
			    'host' => null,
			    'schema' => null,
			    'methods' => array()
			)
		)
	);

For detailed description of route definitions go to `Router` chapter.

## Twig bridge extension

`Config` instance is available in `Twig` templates (if used via `View` component), as `config` variable.
# Config

`Config` object contains frameworks configuration, from error handling options, through components to route definitions.

## Configuration array / bootstrap

Entire configuration is read from plain php arrays, just call `::read($arr)` and pass array containing data to add to or update existing configuration.
Configuration array must have appropriate structure, otherwise `ConfigException` will be thrown.

Configuration is split into sections:

	$arr = array(
		'framework' => array(),
		'container' => array(),
		'dispatcher' => array(),
		'router' => array()
	);

To access section:

	$config = new \Moss\Config\Config(array('section' => array()));
	$section = $config->get('section');

To access specific variable in section:

	$config = new \Moss\Config\Config(array('section' => array('some' => array('var' => 'var'))));
	$var = $config->get('section.some.var');

from PHP > 5.4 can also:

	$config = new \Moss\Config\Config(array('section' => array('some' => array('var' => 'var'))));
    $var = $config->get('section')['some']['var'];

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

## Container

Component definitions can be found in `container` section.
Minimal definition, other array elements are optional

	$arr = array(
		'container' => array(
			'OtherComponentName' => array(
	            'component' => function(\Moss\Container\ContainerInterface $container) {
	                return new \stdClass();
	            },
	            'shared' => false // this is optional, required only for shared components
	        )
		)
	);

For detailed description of constructor and method arguments go to `Container` chapter.

## Dispatcher & event listeners

The `dispatcher` section, holds list of all internal frameworks events.
Each event can have many listeners:

	$arr = array(
		'dispatcher' => array(
			'event.name' => array(
				function(\Moss\Container\ContainerInterface $container) {
                        // do something
                },
                function(\Moss\Container\ContainerInterface $container) {
                        // do something else
                }
			)
		)
	);

As listener supports form of aspect oriented programming, each event is internally split into:

	$arr = array(
		'dispatcher' => array(
			'event.name:before' => array(),
			'event.name' => array(),
			'event.name:after' => array(),
			'event.name:exception' => array(),
		)
	)

For detailed description of method and method arguments go to `Dispatcher` chapter.

## Route definitions

All routes are defined in `router` section. Its an array, where keys are route names, and their values contain route properties.
The simples route definition looks like this:

	$arr = array(
		'router' => array(
			'routeName' => array( // for controller class
			    'pattern' => '/string-controller/',
			    'controller' => '\Moss\Sample\SampleController::index',
			),
			'routeName' => array( // same as above but as callable
                'pattern' => '/callable-controller/',
                'controller' => array('\Moss\Sample\SampleController', 'index'),
            ),
			'otherRoute' => array( // for closure
				'pattern' => '/closure-controller/',
				'controller' => function() {
					return new \Moss\Http\Response\Response('Closure');
				}
			)
		)
	);

Full route definition with sample values

	$arr = array(
		'router' => array(
			'routeName' => array(
			    'pattern' => '/{foo:\w}/({bar:\d})/',
			    'controller' => '\Moss\Sample\SampleController::index',
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

## Import

Config allows for importing data from other arrays.

	$config = new \Moss\Config\Config();
	$config->import(array('section' => array('some' => array('var' => 'var'))));

Existing data will be overwritten, rest will be merged recursively.

Also there can be defined additional arrays that will be imported in `import` section

    'import' => array(
        'sample' => (array) require __DIR__ . '/../src/Moss/Sample/bootstrap.php'
    ),

This data will be imported ie merged as mentioned above.
Since imported array has non-numeric key, all nodes in every imported section will be prefixed eg if prefix was `sample` and node was named as `some_component` it will be prefixed as `sample:some_component`

## Mode

Config also supports modes, that allow for changing configuration by just overwriting existing one.
By default there is no defined mode and data from `import` section will be imported.
When mode is set to `dev`, aside from `import` also `import_dev` section will be imported.

    'import' => array(
        'sample' => (array) require __DIR__ . '/../src/Moss/Sample/bootstrap.php'
    ),
    'import_dev' => array(
        'sample' => (array) require __DIR__ . '/../src/Moss/Sample/bootstrap_dev.php'
    ),

Mode can be set via constructor or by calling `::mode($mode)` method.

## View

`Config` instance is available in `View` under `config` variable.

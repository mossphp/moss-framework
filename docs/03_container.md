# Dependency Injection Container

Sometimes referred to as _Service Container_. DI Container is something that combines repository and factory into one.
Its main task is to instantiate components with all dependencies when they are needed.
Also, container controls number of instances (one _shared instance_ or multiple) and holds additional properties.

In short - DI Container replaces singletons and global variables.

## Component represented as closure

Just create a function that returns object instance.
If component depends on other components, just add `$container` as first argument and call them:

	$component = function(\moss\container\ContainerInterface $container) {
		return $container->get('request')->isAjax();
	}

## Component represented as class

Create component definition for class `Foo` with constructor arguments `arguments`.

	$component = new \moss\container\Component('Foo', $arguments);

If after instantiation definition should call methods, add third parameter to constructor containing array of methods with their arguments eg.:

	$calls = array(
		'method1' => array('array', 'of', 'arguments')
		'method2' => array('array', 'of', 'arguments')
	);
	$component = new \moss\container\Component('Foo', $arguments, $calls);

**Order of elements in argument arrays must correspond to constructor/method arguments.**

To reference other component as argument use prefix `@`, eg.:

	$component = new \moss\container\Component('Foo', array('@Bar'));

Component retrieval is performed by calling `get()` method:

	$component = new \moss\container\Component('Foo');
	$Foo = $component->get($container);

**Component class is provided in case when default bootstrap file is replaced by any textual configuration, eg `YAML`**

## Register component, closure or value and instance

Container can register four component types.

_Component definition_ which is described above under `componentName`:

	$container = new \moss\container\Container();
	$container->register('componentName', $component);

_closure_ under `closureName`:

	$container->register('closureName', function($container) {
		return 'closureBody';
	});

_value_ under `valueName`:

	$container->register('valueName', 'SomeValue');

or some _instance_ for later use:

	$obj = new \stdClsss();
	$container->register('someInstance', $obj);

## Shared

Each definition, whether it is _component_ or _closure_ can be set as **shared**.

	$container->register('sharedComponent', $component, true);

If so, after first instantiation their instaces are preserved and returned by reference in future calls.
As a result - there can be only one instance of shared definition (just like singleton but better).
For example, `Config`, `Router` and `Request` are registered as shared components, but the `View` may have any number of instances.

**It is important to remember that _values_ and registered _instances_ are always shared**.

## Component retrieval

Just call `$component = $container->get('componentIdentifier');` and thats it.
E.g.:

	$Request = $container->get('Request');
	if($Request->isAjax()) {
		echo 'Its Ajax request';
	}

To access some values stored in container:

	$db = $container->get('database'); // array('user' => 'foo', 'pass' => 'bar', 'table' => 'yada');
	$dbUser = $container->get('database.user'); // foo

## Framework components

Framework by default registers the following components:

 * `Config` - configuration instance (_shared_),
 * `Cookie` - cookie wrapper instance (_shared_)
 * `Dispatcher` - event dispatcher (_shared_)
 * `Request` - received request (_shared_)
 * `Router` - router instance (_shared_)
 * `Session` - session wrapper instance (_shared_)

 and of course `Sontainer` itself, all under lowercase names (eg. `config` for `Config`)

The components below are by default defined in configuration

 * `Logger` - logger compatible with psr-3 (_shared_)
 * `View` - View component, convenient template engine wrapper
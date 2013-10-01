# Dependency Injection Container

Sometimes referred to as _Service Container_. DI Container is something that combines repository and factory into one.
Its main task is to instantiate components with all dependencies when they are needed.
Also, container controls number of instances (one _shared instance_ or multiple) and holds additional properties.

In short - DI Container replaces singletons and global variables.

## Component represented as closure

Just create a function that returns object instance.
If component depends on other components, just add `$Container` as first argument.

	$component = function(\moss\container\ContainerInterface $Container) {
		return 'closureBody';
	}

## Component represented as class

Create component definition for class `Foo` with constructor arguments `arguments`.

	$Component = new \moss\container\Component('Foo', $arguments);

If after instantiation definition should call methods, add third parameter to constructor containing array of methods with their arguments eg.:

	$calls = array(
		'method1' => array('array', 'of', 'arguments')
		'method2' => array('array', 'of', 'arguments')
	);
	$Component = new \moss\container\Component('Foo', $arguments, $calls);

**Order of elements in argument arrays must correspond to constructor/method arguments.**

To reference other component as argument use prefix `@`, eg.:

	$Component = new \moss\container\Component('Foo', array('@Bar'));

Component retrieval is performed by calling `get()` method:

	$Component = new \moss\container\Component('Foo');
	$Foo = $Component->get($Container);

**Component class is provided in case when default bootstrap file is replaced by any textual configuration, eg `YAML`**

## Register component, closure or value and instance

Container can register four component types.

_Component definition_ which is described above under `componentName`:

	$Container = new \moss\container\Container();
	$Container->register('componentName', $Component);

_closure_ under `closureName`:

	$Container->register('closureName', function($Container) {
		return 'closureBody';
	});

_value_ under `valueName`:

	$Container->register('valueName', 'SomeValue');

or some _instance_ for later use:

	$obj = new \stdClsss();
	$Container->register('someInstance', $obj);

## Shared

Each definition, whether it is _component_ or _closure_ can be set as **shared**.

	$Container->register('sharedComponent', $Component, true);

If so, after first instantiation their instaces are preserved and returned by reference in future calls.
As a result - there can be only one instance of shared definition (just like singleton but better).
For example, `Config`, `Router` and `Request` are registered as shared components, but the `View` may have any number of instances.

**It is important to remember that _values_ and registered _instances_ are always shared**.

## Component retrieval

Just call `$Component = $Container->get('componentIdentifier');` and thats it.
E.g.:

	$Request = $Container->get('Request');
	if($Request->isAjax()) {
		echo 'Its Ajax request';
	}

To access some values stored in container:

	$db = $Container->get('database'); // array('user' => 'foo', 'pass' => 'bar', 'table' => 'yada');
	$dbUser = $Container->get('database.user'); // foo

## Framework components

Framework always registers the following components:

 * `Config` - configuration instance (_shared_),
 * `Container` - DI container (_shared_)
 * `Cookie` - cookie wrapper instance (_shared_)
 * `Dispatcher` - event dispatcher (_shared_)
 * `Logger` - logger compatible with psr (_shared_)
 * `Request` - received request (_shared_)
 * `Router` - router instance (_shared_)
 * `Session` - session wrapper instance (_shared_)

The components below are by default defined in configuration

 * `Twig` - Twig template engine, used by View component
 * `View` - View component, convinient template engine wrapper
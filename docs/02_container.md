# Dependency Injection Container

## Component - class

Create component definition for class `Foo` with constructor arguments `arguments`.

	$Component = new \Moss\container\Component('Foo', $arguments);

If after instantiation, definition should call methods, add third parameter to constructor containing array of methods with their arguments eg.:

	$calls = array(
		'method1' => array('array', 'of', 'arguments')
		'method2' => array('array', 'of', 'arguments')
	);
	$Component = new \Moss\container\Component('Foo', $arguments, $calls);

**Order of elements in argument arrays must correspond to constructor/method arguments.**

To reference other component as argument use prefix `@`, eg.:

	$Component = new \Moss\container\Component('Foo', array('@Bar'));

Component retrieval is performed by calling `get()` method:

	$Component = new \Moss\container\Component('Foo');
	$Foo = $Component->get($Container);

## Register component, closure or value and instance

Container can register four component types.

_Component definition_ which is described above under `componentName`:

	$Container = new \Moss\container\Container();
	$Container->register('componentName', $Component);

_closure_ under `closureName`:

	$Container->register('closureName', function($Container) { return 'closureBody'; });

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

**It is important to remember that _values_ and registered _instances_ are always shared**.

## Component retrieval

Just call `$Component = $Container->get('componentIdentifier');` and thats it.

## Framework components

 * Config - configuration instance (_shared_),
 * Container - DI container (_shared_)
 * Cookie - cookie wrapper instance (_shared_)
 * Dispatcher - event dispatcher (_shared_)
 * Logger - logger compatible with psr (_shared_)
 * Request - received request (_shared_)
 * Router - router instance (_shared_)
 * Session - session wrapper instance (_shared_)
 * Twig - Twig template engine, used by View component
 * View - View component
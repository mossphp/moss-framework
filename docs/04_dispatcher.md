# Event dipatcher

## Event listener

Create event listener, that will call `method` with array of `arguments` on `component` when event occours (event is defined elswhere).
Method and arguments are optional.

	$Listener = new \Moss\dispatcher\Listener('component', 'method', $arguments);

Arguments are defined just like in DI component's definitions, with two additional - predefined - components: `Subject` and `Message`.
The `Subject` is an event object (usually object firing event), while `Message` is a text assiociated with event (eg. exception message).

To retrieve listening effect call `get` method on defined listener.
Retrieval requires object implementing `\Moss\container\ContainerInterface`, other attributes are optional.

	$Result = $Listener->get($Container, $Subject, $Message);

## Register listener to event

Register defined `$Listener` to observe `foo` event:

	$Dispatcher = new \Moss\dispatcher\Dispatcher($Container);
	$Dispatcher->register('foo', $Listener);

Or register closure as event listener:

	$Dispatcher->register('foo', function($Container, $Subject = null, $Message = null) { return 'ClosureListenerResult'; });

Or to multiple events:

	$Dispatcher->register(array('foo', 'bar', 'yada'), $Listener);

Generally order in which listeners are registered to event is same oreder in which they are called.
To change this, when registering listener set third argument - `priority` - zero is first.

## Fire event

To fire `foo` event, call:

	$Dispatcher->fire('foo');

All defined listeners, that observe `foo` event will be notified.

## Aspects

When `foo` event is fired, `Dispatcher` actually fires tree events (called _aspects_) - `foo:before`, `foo` and `foo:after` - in that order.
If any of those events, throws unhandled exception `foo:exception` will be fired - and no further listeners will be notified.
In case when `foo:exception` has no listeners, exception will be rethrown.

In case of `:exception`, the `Subject` is thrown exception and `Message` is its message.

Registering aspect listeners is identical as registering normal event.

## Framework events

Framework has basic events, fired when kernel reaches specific point (in that order):

 * `kernel.request` - when received request,
 * `kernel.route` - when found route matching request
 * `kernel.controller` - just before calling controller
 * `kernel.response` - after receiving response from controller
 * `kernel.send` - just before sending response to client

There are additional tree events, occuring when something went wrong:

 * `kernel.403` - fired when SecurityException is thrown,
 * `kernel.404` - fired when RouterException is thrown,
 * `kernel.500` - fired when other exception is thrown



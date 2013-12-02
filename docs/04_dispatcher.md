# Event dispatcher

Event dispatcher is similar to _observer pattern_, its task is respond to fired events.
When event is fired, dispatcher checks if event is defined and has listeners.
If so - all listeners (either class or closure) are notified about event.

## Event listener

Create event listener, that will call `method` with array of `arguments` on `component` when event occurred (event is defined elsewhere).
Method and arguments are optional.

	$listener = new \moss\dispatcher\Listener('component', 'method', $arguments);

Arguments are defined just like in `container` component's definitions, with two additional - predefined - components: `subject` and `message`.
The `subject` is an object, usually the one firing event (eg.exception), while `message` is a text associated with event (eg. exception message).

To retrieve listening effect call `get` method on defined listener.
Retrieval requires object implementing `\moss\container\ContainerInterface`, other attributes are optional.

	$result = $listener->get($container, $subject, $message);

## Register listener to event

Register defined `$listener` to observe `foo` event:

	$dispatcher = new \moss\dispatcher\Dispatcher($container);
	$dispatcher->register('foo', $listener);

Or register closure as event listener:

	$dispatcher->register('foo', function($container, $subject = null, $message = null) {
		return 'ClosureListenerResult';
	});

Or to multiple events:

	$Dispatcher->register(array('foo', 'bar', 'yada'), $listener);

Generally order in which listeners are registered to event is same order in which they are called.
To change this, when registering listener set third argument - `priority` - zero is first.

## Fire event

To fire `foo` event, call:

	$dispatcher->fire('foo');

All defined listeners, that observe `foo` event will be notified.

## Stop

To stop other listeners from being notified, call `::stop()` method.

	$dispatcher->stop();

No other listeners will be notified about ongoing event.

## Aspects

When `foo` event is fired, `dispatcher` actually fires three events (called _aspects_) - `foo:before`, `foo` and `foo:after` - in that order.
If any of those events, throws unhandled exception `foo:exception` will be fired - and no further listeners will be notified.
In case when `foo:exception` has no listeners, exception will be rethrown.

In case of `:exception`, the `subject` is thrown exception and `message` is its message.

Registering aspect listeners is identical as registering normal event, just remember aspect name postfix `:before`, `:after` and `:exception`.

## Framework events

Framework has few basic events, fired when kernel reaches specific point (in that order):

 * `kernel.request` - when received request,
 * `kernel.route` - when found route matching request
 * `kernel.controller` - just before calling controller
 * `kernel.response` - after receiving response from controller
 * `kernel.send` - just before sending response to client

There are additional three events, occurring when something went wrong:

 * `kernel.403` - fired when SecurityException is thrown,
 * `kernel.404` - fired when RouterException is thrown,
 * `kernel.500` - fired when other exception is thrown



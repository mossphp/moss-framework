# Flow

 1. Users request hits `./web/index.php` which is entry point for all request, whether they are from browser or console,
 1. configuration is read from bootstrap (default: `./web/bootstrap.php`) file,
 1. instance of following components are created and configured: `Config`, `Container`, `Dispatcher`, `Router`,
 1. `Session`, `Cookie` and `Request` instances are created,
 1. `Request` is passed to `Kernel`,
 1. `Kernel` fires `kernel.request` event,
 1. `Kernel` passes `Request` to `Router` to find matching `Route`, if found fires `kernel.route` event
 1. `Kernel` fires `kernel.controller` event, after which calls controller from matching `Route`,
 1. `Kernel` receives `Response` from called controller and fires `kernel.response` event
 1. `Kernel` fires `kernel.send` and returns response to `index.php` where it is sent back to user.

When controller does not return instance of `ResponseInterface` an `KernelException` exception is raised.
As soon as fired event listener returns an instance of `ResponseInterface`, `Kernel` jumps to last item on list - fires last event and returns `Response` to user.

If `SecurityException` is thrown, `kernel.403` event is fired, in case of `RouterException` - `kernel.404` is fired.
Every other exception fires `kernel.500` event.
If fired event returns `ResponseInterface` - `Kernel` behaves same as in every other event - skipps all steps, except last one and sends `Response` to user.
If not, defined error handler ... handles exception.

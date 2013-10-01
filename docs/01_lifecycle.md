# Request lifecycle & Kernel

 1. Incoming request hits `./web/index.php` which is entry point for all requests, whether they are from browser or console,
 1. Configuration is read from bootstrap file (default: `./web/bootstrap.php`),
 1. Creates instances of the following components:
    * `Config` - where all framework configuration is held,
    * `Container` which provides acces to other components and services,
    * `Dispatcher` - that handles all events and their listeners,
    * `Router` - responsible for URL handling,
    * `Request` - with `Session`, `Cookie` representing received request,
 1. `Request` is passed to `Kernel`, which fires `kernel.request` event,
 1. `Kernel` passes `Request` to `Router` to find matching `Route`, if found fires `kernel.route` event,
 1. `Kernel` fires `kernel.controller` event, and calls controller from matching `Route` - either as closure or class method (action),
 1. When `Controller` returns instance of `ResponseInterface` event `kernel.response` is fired
 1. Just before sending response back to user `Kernel` fires `kernel.send` and returns response to `index.php` where it's sent.

When returned response is not an instance of `ResponseInterface` an `KernelException` exception is thrown.
If `SecurityException` is thrown, `kernel.403` event is fired, in case of `RouterException` - `kernel.404` is fired.
Every other exception fires `kernel.500` event.
If fired event returns `ResponseInterface` - `Kernel` behaves same as in every other event - jumps to last step and sends `Response` to user.
If not, defined error handler ... handles exception.

When event is fired event listeners will be called for response.
As soon as any of them returns an instance of `ResponseInterface`, `Kernel` jumps to last item on above list - fires last event and returns `Response` to user.

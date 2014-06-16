# Request lifecycle & Kernel

 1. Incoming request hits `./web/index.php` which is entry point for all requests, whether they are from browser or console,
 1. `App` is started
 1. `App` reads configuration from bootstrap files (default: `./web/bootstrap.php`),
 1. Creates instances of the following components (available from `App` under lowercase names):
    * `Config` - where all framework configuration is held,
    * `Container` which provides access to other components and services,
    * `Dispatcher` - that handles all events and their listeners,
    * `Router` - responsible for URL handling,
    * `Request` - with `Session`, `Cookie` represents received request,
 1. Received request `Request` is handled - event `kernel.request` is fired,
 1. `App` passes `Request` to `Router` to find matching `Route`, if found - fires `kernel.route` event,
 1. `Kernel` fires `kernel.controller` event, and calls controller from matching `Route` - either as closure or other callable,
 1. When `Controller` returns instance of `ResponseInterface` event `kernel.response` is fired
 1. Just before sending response back to user `Kernel` fires `kernel.send` and returns response to `index.php` where it's sent.

When returned response is not an instance of `ResponseInterface` an `AppException` exception is thrown.
If `ForbiddenException` is thrown, `kernel.403` event is fired, in case of `NotFoundException` - `kernel.404` is fired.
Every other exception fires `kernel.500` event.
`ForbiddenException` and `NotFoundException` can be also used in controllers.

If fired event returns `ResponseInterface` - `App` behaves same as in every other event - jumps to last step and sends returned `Response` to user.
If not, defined exception handler ... handles exception.
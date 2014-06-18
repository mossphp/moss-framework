# Request lifecycle & Kernel

 1. Incoming request hits `./web/index.php` which is entry point for all requests, whether they are from browser or console,
 1. `App` is started and receives configuration arrays (optional)
 1. Creates instances of the following components (available from `App` under lowercase names):
    * `Config` - where all framework configuration is held,
    * `Container` which provides access to other components and services,
    * `Dispatcher` - that handles all events and their listeners,
    * `Router` - responsible for URL handling,
    * `Request` - represents received request (with `Session`, `Cookie` also available just like the rest),
 1. Before received request `Request` is passed to router - `kernel.request` event is fired,
 1. `Router` tries to match `Request` to one of registered `Route` instances, if found - fires `kernel.route` event,
 1. Then `App` fires `kernel.controller` event, and calls controller from matching `Route` - either as closure or other callable,
 1. When `Controller` returns instance of `ResponseInterface` `kernel.response` event is fired,
 1. Just before sending response back to user `App` fires `kernel.send` and returns response to `index.php` where it's sent.

Controller must return instance of `ResponseInterface` or at least string (that will be converted to plain text response).

When returned response is not an instance of `ResponseInterface` an `AppException` exception is thrown.

If `ForbiddenException` is thrown, `kernel.403` event is fired, in case of `NotFoundException` - `kernel.404` is fired.
`ForbiddenException` and `NotFoundException` can be also used in controllers.

Every other exception fires `kernel.500` event.

Internal kernel events must return `null` or `ResponseInterface`.
If `null` is returned, nothing happens and frameworks works as mentioned above.
In case when `ResponseInterface` is returned - `App` jumps to last step (just before `kernel.send`) and sends returned `Response` to user.
If not, defined exception handler ... handles exception.
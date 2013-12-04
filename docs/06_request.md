# Request

The `Request` object represents incoming request with all handy properties and stuff.
Just create an instance, with optional `$_SESSION` and `$_COOKIE` wrappers:

	$request = new Request();

## Basic methods

 * `isAjax` - will return `true` if request was sent with `XMLHttpRequest` header (it means that it is AJAX request - prototype.js and jQuery do send that header)
 * `baseName` - returns basename - if available,
 * `clientIp` - tries to resolve clients ip or his proxy
 * `controller` - will return controller identifier if was passed in query or matching route found (this can be string or closure)
 * `url` - returns requested url
 * `referrer` - from where did request came
 * `locale` - requested language
 * `format` - requested format - mainly null - which means any format

## Headers and server

Request headers are available via the `::getHeader($header)` method, where `$header` is headers name in lowercase and `-` changed to `_`, eg: `$Request->getHeader('content_type')` will return `Content-Type` or `null` if header not set.
Environment variables (`$_SERVER`) are accessible via `::getServer($server)` method. Their names are same as in `$_SERVER` superglobal. The `::getServer()` method will return `null` if environment variable is not set.

## Console and other methods

To access query (`GET`) arguments use `::query->get($key, $value = null)` method, same for console (`CLI`) arguments.
For `POST`, `PUT`, `DELETE` arguments call `::post->get($key, $value = null)` method.

Both methods allow access to multidimensional arrays, just separate keys with `.` (dot) eg:

	$yada = $request->post->get('foo.bar.yada'); // $_POST['foo']['bar']['yada'];

Or via method:

	$yada = $request->post()->get('foo.bar.yada'); // $_POST['foo']['bar']['yada'];

To set `GET` and `POST` values use respectively `setQuery` and `setPost`

## Files

The `::files->get($key)` method grants access to modified `$_FILES` superglobal.
Structure has been modified as follows:

	// <input type="file" name="foo[bar][yada]"/>
	$tmp_name = $_FILES['foo']['tmp_name']['bar']['yada'];
	$tmp_name = $Request->files->get('foo.bar.yada.tmp_name'); // as $tmp_name = $_files['foo']['bar']['yada']['tmp_name'];

In addition, each field receives additional property `error_text` which will contain error message (if error occurred, empty otherwise)

## Cookies and session

By default, framework uses its own session and cookie wrappers that - for compliance with `$_SESSION` and `$_COOKIE` superglobals - implemnent ArrayAccess interface.
If those are not passed in `::__constructor` - `Request` will use native superglobals.

Access to them is available trough `::session->get($key, $value = null)` and `::cookie->get($key, $value = null)` methods - which are similar to `::query->get()` and `::post->get()` methods.

## Additional methods

 * `method` - returns request method
 * `schema` - returns request protocol
 * `host` - returns host on which request is handled
 * `dir` - returns directory (relative to baseName)

## Twig bridge extension

`Request` instance is available in `Twig` templates (if used via `View` component), as `request` variable.
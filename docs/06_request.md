# Request

The `Request` object represents incoming request with all handy properties and stuff.
Just create an instance, with optional `$_SESSION` and `$_COOKIE` wrappers:

	$Request = new Request();

## Basic methods

 * `isXHR` - will return `true` if request was sent with `XMLHttpRequest` header (it means that it is AJAX request - prototype.js and jQuery do send that header)
 * `baseName` - returns basename - if available,
 * `clientIp` - tries to resolve clients ip or his proxy
 * `controller` - will return controller identifier if was passed in query or matching route found
 * `url` - returns requested url
 * `referer` - from where did request came
 * `locale` - requested language
 * `format` - requested format - generally null - which means any format

## Headers and server

Request headers are available via the `::header($header)` method, where `$header` is headers name in lowercase and `-` changed to `_`, eg: `$Request->header('content_type')` will return `Content-Type` or `null` if header not set.
Environment variables (`$_SERVER`) are accessible via `::server($server)` method. Theri names are same as in `$_SERVER` superglobal. The `::server()` method will return `null` if requested variable is not set.

## Console and other methods

To access query (`GET`) arguments use `::query($key, $value = null)` method, same for console (`CLI`) arguments.
For `POST`, `PUT`, `DELETE` arguments call `::post($key, $value = null)` method.

Both methos allow access to multidimensional arrays, just separate keys with `.` (dot) eg:

	$yada = $Request->post('foo.bar.yada'); // $_POST['foo']['bar']['yada'];

If value unequals `null`, new value will be set.

## Files

The `::file($key)` method grants acces to modified `$_FILES` superglobal.
Structure has been modified as follows:

	// <input type="file" name="foo[bar][yada]"/>
	$tmp_name = $_FILES['foo']['tmp_name']['bar']['yada'];
	$tmp_name = $Request->file('foo.bar.yada.tmp_name'); // as $tmp_name = $_files['foo']['bar']['yada']['tmp_name'];

In addition, each field receives additional property `error_text` whitch will contain error message (if error occoured)

## Cookies and session

By default, framework uses its own session and cookie wrappers that - for compliance with `$_SESSION` and `$_COOKIE` superglobals - implemnent ArrayAccess interface.
If those are not passed in `::__constructor` - `Request` will use native superglobals.

Access to them is available trough `::session($key, $value = null)` and `::cookie($key, $value = null)` methods - which are similar to `::query()` and `::post()` methods.

## Additional methods

 * `method` - returns request method
 * `schema` - returns request protocol
 * `host` - returns host on which request is handled
 * `dir` - returns directory (relative to baseName)

## Twig bridge extension

`Request` instance is available in `Twig` templates (if used via `View` component), as `Request` variable.
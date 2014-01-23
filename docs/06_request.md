# Request

The `Request` object represents incoming request with all handy properties and stuff.
Just create an instance, with optional `$_SESSION` and `$_COOKIE` wrappers:

	$request = new Request();

## Basic methods

 * `isAjax` - will return `true` if request was sent with `XMLHttpRequest` header (it means that it is AJAX request - prototype.js and jQuery do send that header)
 * `baseName` - returns basename - if available,
 * `clientIp` - tries to resolve clients ip or his proxy
 * `controller` - will return controller identifier if was passed in query or matching route found (this can be string or closure)
 * `uri` - returns requested uri
 * `path` - returns requested path (relative to script location)
 * `referrer` - from where did request came
 * `locale` - requested language
 * `format` - requested format - mainly null - which means any format

## Headers and server

Request headers are available via the `::getHeader($header)` method, where `$header` is headers name in lowercase and `-` changed to `_`, eg: `$Request->getHeader('content_type')` will return `Content-Type` or `null` if header not set.
Environment variables (`$_SERVER`) are accessible via `::getServer($server)` method. Their names are same as in `$_SERVER` superglobal. The `::getServer()` method will return `null` if environment variable is not set.

## GET, POST, PUT and DELETE

To access query (`GET`) arguments use `::query->get($key, $value = null)` method
For `POST`, `PUT`, `DELETE` arguments call `::post()->get($key, $value = null)` method.

Both methods allow access to multidimensional arrays, just separate keys with `.` (dot) eg:

	$yada = $request->post->get('foo.bar.yada'); // $_POST['foo']['bar']['yada'];

Or via method:

	$yada = $request->post()->get('foo.bar.yada'); // $_POST['foo']['bar']['yada'];

To set `GET` and `POST` values use respectively `setQuery` and `setPost`

## Console, aka CLI method

Framework can be run from console, just type:

	php ./web/index.php [arguments]

First unnamed argument will be put in the `path` property and resolved by `Router` just like friendly link.
Fallowing unnamed arguments and all named arguments will be available in the same way as `GET` arguments.
Named arguments without value are treated as true flags.

 * `foo` - unnamed argument
 * `-foo` - named argument without value (true flag), to treat argument as named, there must be at least one `-`
 * `--foo=bar` - named argument with string value
 * `--foo=[1,2,3]` - named argument with array value
 * `--foo={a:1,b:2,c:3}` - named argument with associative array value

Eg:

	php ./web/index.php /foo/bar --foo --bar=[1, 2, 3, 4] --yada={a:1, b:2, c:3, d:4}

Will request `/foo/bar` route with arguments:

	array(
		'foo' => true,
		'bar' => array(1, 2, 3, 4),
		'yada' => array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4)
	)

## Files

The `::files->get($key)` method grants access to `FilesBag`, which represents a little bit modified `$_FILES` superglobal.
Structure has been modified as follows:

	// <input type="file" name="foo[bar][yada]"/>
	$tmp_name = $_FILES['foo']['tmp_name']['bar']['yada'];
	$tmp_name = $Request->files()->get('foo.bar.yada.tmp_name'); // as $tmp_name = $_files['foo']['bar']['yada']['tmp_name'];

To upload file (to move uploaded file) from above field, just call:

	$Request->files()->uploaded('foo.bar.yada')->move('./some/directory/', 'newFileName');

The `::uploaded()` method returns instance of `UploadedFile` which simplifies file upload.

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
# Response

`Response` represents all the things that are sent to users in response to his `Request`.
Sending `Response` to client:

	$Reponse = new \moss\http\response\Response($responseContent);
	$Response->send();

This will output HTML response (`Content-Type: text/html`) with `status code` `200` (`OK`).

## Content type & Status

When creating response, you set (your own or default values) responses `Content-Type` and its `status code`.
Content type defines whar response returns to user, if it is plain text, `HTML` or `PDF`.
While `status code` defines what response means, eg: `200` means everything is OK, `404` means `not found`, `500` serwer error, and so on.

## Additional headers

To add, change or remove header from response use `::header($header, $value)` method:

	$Response->setHeader('Content-Type', 'text/plain'); // set header
	$Response->setHeader('Content-Type', 'text/html'); // overwrite previous

To remove header

	$Response->removeHeader('Content-Type');

To retrieve header:

	$header = $Response->getHeader('Content-Type');
	$header = $Response->getHeader('Content-Type', 'default-value-when-header-does-not-exist');

## Redirect

There is different response object - `RedirectResponse`. Its purpose is to redirect user to new url.

	$Redirect = new \moss\http\response\RedirectResponse('http://google.com');
	$Redirect->send();

`RedirectResponse` extends `Response`, main difference are: `::__construct($address, $delay = 0)` and `::address($address = null)`, `::delay($delay = null)`

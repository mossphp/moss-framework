# MOSS Micro Framework

[![Build Status](https://travis-ci.org/potfur/moss.png?branch=master)](https://travis-ci.org/potfur/moss)

For licence details see LICENCE.md
Documentation is available in ./docs/

## About

_**Moss** - to relax or chill, the act of chilling_

`Moss` is a small almost micro (but not another Sinatra wannabe) framework, providing basic tools that can be used to build simple web pages or APIs.

So what's the difference between other micro-frameworks?
`Moss` isn't some kind of cropped full stack framework, that was cut down to fit into _micro_ segment.
Neither one of those minimalistic, closure lovers :)

`Moss` was developed a solution with small footprint, easily to extend, with as small dependencies as possible (actually - none).

But still, `Moss framework` wants to be _fashionable_ and follows trends: `closures`, `event dispatching`, `dependency injection`, `aspect oriented programming`

## Features

 * fully grown `Router` (not powerful but working :) ),
 * `Request` and `Response` objects (got http auth, and easy header management),
 * easy file upload trough `Request::file` methods
 * flash messages
 * dependency injection container
 * event dispatcher with `AOP`
 * closure and class controllers (that can be organized into bundles with fluent directory structure),
 * simple view that can be easily extended to use `Twig` (as package in composer)
 * and clean code and very loose coupling
 * and more to come

## Quickstart

Download from [github](https://github.com/potfur/moss)

Create new controller `./src/Quick/Start/Controller/QuickController.php` containing:

```php
namespace Quick\Start\Controller;

use Moss\Container\Container;
use Moss\Http\Response\Response;

class DemoController
{
	protected $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function indexAction()
	{
		return new Response(__METHOD__);
	}
}
```

Now when you call `http://127.0.0.1/moss/web/?controller=Quick_Start_Demo_index` assuming that framework is available under http://127.0.0.1/moss/web/,
you should see method name `Quick\Start\Controller\DemoController::indexAction`

To register route to that action that allows to enter `http://127.0.0.1/moss/web/quick-start/`, in `./web/bootstrap.php` in section `router` add

```php
'index' => array(
    'pattern' => '/quick-start/',
    'controller' => 'Quick:Start:Demo:index'
)
```

And that's it, the rest depends on your needs and skills.

## Contribute

If you want to submit fix or some other enhancements, feel free to do so.
Whenever you find a bug it would be nice if you submit it.
And if you submit fix - this would be truly amazing!

### How to Contribute

 * Fork;
 * Create a new branch for each feature/improvement/issue;
 * Send a pull request from branch

### Style Guide

All pull requests must adhere to the PSR-2 standard.
All pull requests must be accompanied by passing PHPUnit tests.

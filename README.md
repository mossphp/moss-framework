# MOSS Micro Framework

[![Build Status](https://travis-ci.org/mossphp/moss-framework.png?branch=master)](https://travis-ci.org/mossphp/moss-framework)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mossphp/moss-framework/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mossphp/moss-framework/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/mossphp/moss-framework/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mossphp/moss-framework/?branch=master)

For licence details see LICENCE.md
Documentation is available on Wiki : [https://github.com/mossphp/moss-framework/wiki](https://github.com/mossphp/moss-framework/wiki)

## About

Welcome to `Moss` a micro framework, that provides basic tools for building simple web pages or APIs it can also handle something bigger.

So what's the difference between other micro-frameworks?

`Moss` isn't some kind of cropped full stack framework, that was cut down to fit into _micro_ segment.

`Moss` was developed as a solution with small footprint, that is easy to extend and with as little dependencies as possible - and still simple to use.

`Moss` also wants to be _fashionable_ and follows trends: `dependency injection`, `event dispatching`, `request-response objects`, `clean code`.
 Not because its fancy to be _trendy_, but because it makes sense and code benefits from them. 

# Features

 * fully grown `Router` (not powerful but working :) ),
 * `Request` and `Response` objects (got http auth, and easy header management),
 * flash messages
 * dependency injection container
 * event dispatcher with `AOP`
 * closure and class controllers (that can be organized into bundles with fluent directory structure),
 * simple view that can be easily extended with bridge to use `Twig` (as package in composer)
 * and clean code
 * and more
 
# Documentation

Documentation is available on Wiki : [https://github.com/mossphp/moss-framework/wiki](https://github.com/mossphp/moss-framework/wiki)

# Quickstart

Add to `composer.json`:

```json
	{
	    "require": {
	        "moss/framework": "*"
	    }
	}
```

Or from console

```
	php composer.phar require moss/framework
```

Then create entry file, eg `./web/index.php` containing:

```php
	<?php
	use Moss\Config\Config;
	use Moss\Container\ContainerInterface;
	use Moss\Http\Response\Response;
	use Moss\Kernel\App;
	
	require __DIR__ . '/vendor/autoload.php';
	
	$app = new App(new Config());
	$app->route('main', '/', function () { return new Response('Hello World'); });
	$app->run()->send();
```

Or download simple sample app from [github](https://github.com/mossphp/moss-demo-app)

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
All pull requests should be accompanied by passing PHPUnit tests.
Add phpDocs to all methods, including at least a description, all @param, @return and @throws declaration


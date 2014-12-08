# MOSS Micro Framework

[![Build Status](https://travis-ci.org/potfur/moss-framework.png?branch=master)](https://travis-ci.org/potfur/moss-framework)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/potfur/moss-framework/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/potfur/moss-framework/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/potfur/moss-framework/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/potfur/moss-framework/?branch=master)

For licence details see LICENCE.md
Documentation is available in ./docs/

## About

_**Moss** - to relax or chill, the act of chilling_

`Moss` is a small almost micro (but not another Sinatra wannabe) framework, providing basic tools that can be used to build simple web pages or APIs.

So what's the difference between other micro-frameworks?
`Moss` isn't some kind of cropped full stack framework, that was cut down to fit into _micro_ segment.
Neither one of those minimalistic, closure lovers :)

`Moss` was developed a solution with small footprint, easily to extend, with as small dependencies as possible (actually - none).

But still, `Moss framework` wants to be _fashionable_ and follows trends: `closures`, `event dispatching`, `dependency injection`, `aspect oriented programming`.
Why there's no mention about `MVC`? Because `Moss` does not implement it. Instead - gives freedom to do it in your favorite way.

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

Add to `composer.json`:

	{
	    "require": {
	        "moss/framework": ">=1.0"
	    }
	}

Then create entry file, eg `./web/index.php` containing:

	<?php
	require __DIR__ . '/../vendor/autoload.php';

	$moss = new \Moss\Kernel\App();
	$moss->route('main', '/', function () { return 'Hello world'; });
	$moss->run()
	    ->send();

Or download simple sample app from [github](https://github.com/potfur/moss-demo-app)

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
Add phpDocs to all methods, including at least a description, all @param declarations and the @return declaration


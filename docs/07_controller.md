# Controller

Controller can be represented as closure or object with action methods.
In both cases, value returned by closure or action, must be instance of `ResponseInterface`, otherwise exception will be thrown.

## As closure

If controller is represented by closure situation is simple:

	function() {
		return new \moss\http\response\Response('Hello world');
	}

When calling closure controller, `Kernel` passes tree arguments:

 1 instance of `ContainerInterface` to grant access to other components/services
 1 instance of `RouterInterface` to help in URL creation
 1 instance of `RequestInterface` for easy access to request data

	function(ContainerInterface $Container, RouterInterface $Router, RequestInterface $Request) {
		// ...
	}

## Non-closure controller - class with actions

In case of class controllers, arguments passed to closure controller are passe to class constructor.
Therefore, action parameters can be used as needed, but must be optional.

	class SomeController {
		public function __controller(ContainerInterface $Container, RouterInterface $Router, RequestInterface $Request) {
			// ...
		}

		public function someAction();
	}

If controller has `::before()` and `::after(ResponseInterface $Response)` methods, they will be called before and after requested action.
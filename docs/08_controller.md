# Controller

Controller can be represented as closure or object with action methods.
In both cases, value returned by closure or action, must be instance of `ResponseInterface`, otherwise exception will be thrown.

## As closure

If controller is represented by closure situation is simple, closure is injected with `AppInterface` instance (later referred as `App`) and should return string or `ResponseInterface`.

	function(\Moss\Kernel\AppAppInterface $app)
	{
		return new \Moss\Http\Response\Response('Hello world');
	}

## Non-closure controller - class with actions

Similar thing happens in case of class controllers, `App` instance is injected into class constructor.
Therefore, action parameters can be used as needed, but must be optional, otherwise `App` won't be able to call such action.

	class SomeController
	{
		public function __controller(\Moss\Kernel\AppInterface $app)
		{
			// ...
		}

		public function some()
		{
			// ...
		}
	}

If controller has `::before()` and `::after(\Moss\Http\Response\ResponseInterface $Response)` methods, they will be called before and after requested action.

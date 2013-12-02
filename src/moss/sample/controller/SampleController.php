<?php
namespace moss\sample\controller;

use moss\container\ContainerInterface;
use moss\http\request\RequestInterface;
use moss\http\response\Response;
use moss\router\RouterInterface;

/**
 * Class SampleController
 *
 * @package moss\sample\controller
 */
class SampleController
{

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @param RouterInterface    $router
     * @param RequestInterface   $request
     */
    public function __construct(ContainerInterface $container, RouterInterface $router, RequestInterface $request)
    {
        $this->container = & $container;
        $this->router = & $router;
        $this->request = & $request;
    }

    /**
     * Sample method, displays link to controller source
     */
    public function indexAction()
    {
        $uri = $this->router->make('moss:sample:sample:source');

        return new Response('MOSS Sample controller and <a href="' . $uri . '">it looks like this</a>');
    }

    /**
     * Displays controller source
     *
     * @return Response
     */
    public function sourceAction()
    {
        return new Response(highlight_file(__FILE__, true));
    }
}
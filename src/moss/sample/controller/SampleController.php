<?php
namespace moss\sample\controller;

use moss\container\Container;
use moss\http\request\Request;
use moss\http\response\Response;
use moss\http\response\ResponseRedirect;
use moss\router\Router;

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
     * @param Container $container
     * @param Router    $router
     * @param Request   $request
     */
    public function __construct(Container $container, Router $router, Request $request)
    {
        $this->container = & $container;
        $this->router = & $router;
        $this->request = & $request;
    }

    /**
     * Sample method, displays link to controller source
     *
     * @return Response
     */
    public function indexAction()
    {
        $content = $this->container->get('view')
                                   ->template('moss:sample:index')
                                   ->set('method', __METHOD__)
                                   ->render();

        return new Response($content);
    }

    /**
     * Login form
     *
     * @return Response
     */
    public function loginAction()
    {
        if ($this->request->post->get('login') && $this->request->post->get('password')) {
            $result = $this->container->get('security')
                                      ->tokenize($this->request->post->all());

            if ($result) {
                return new ResponseRedirect($this->router->make('moss:sample:sample:source'));
            }
        }

        $content = $this->container->get('view')
                                   ->template('moss:sample:login')
                                   ->set('method', __METHOD__)
                                   ->render();

        return new Response($content);
    }

    /**
     * Logout
     *
     * @return ResponseRedirect
     */
    public function logoutAction()
    {
        $this->container->get('security')
                        ->destroy();

        return new ResponseRedirect($this->router->make('moss:sample:sample:index'));
    }

    /**
     * Displays controllers and bootstrap source
     *
     * @return Response
     */
    public function sourceAction()
    {
        $path = __ROOT__ . '/../src/moss/sample';
        $content = $this->container->get('view')
                                   ->template('moss:sample:source')
                                   ->set('method', __METHOD__)
                                   ->set('controller', highlight_file($path . '/controller/SampleController.php.', 1))
                                   ->set('bootstrap', highlight_file($path . '/bootstrap.php', 1))
                                   ->render();

        return new Response($content);
    }
}
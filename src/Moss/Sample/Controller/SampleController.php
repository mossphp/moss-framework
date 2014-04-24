<?php
namespace Moss\Sample\Controller;

use Moss\Container\Container;
use Moss\Http\Request\Request;
use Moss\Http\Response\Response;
use Moss\Http\Response\ResponseRedirect;
use Moss\Http\Router\Router;
use Moss\Security\AuthenticationException;

/**
 * Class SampleController
 *
 * @package Moss\Sample
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
            ->template('Moss:Sample:index')
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
        return new Response($this->form());
    }

    /**
     * Logging action
     *
     * @return Response
     */
    public function authAction()
    {
        try {
            if (!$this->request->method('post')) {
                throw new AuthenticationException('Unable to authenticate, invalid method');
            }

            $this->container->get('security')
                ->tokenize($this->request->body->all());

            return new ResponseRedirect($this->router->make('source'));
        } catch (AuthenticationException $e) {
            $this->container->get('flash')
                ->add($e->getMessage(), 'error');

            return new Response($this->form(), 401);
        }
    }

    /**
     * Returns rendered login form
     *
     * @return string
     */
    protected function form()
    {
        return $this->container->get('view')
            ->template('Moss:Sample:login')
            ->set('method', __METHOD__)
            ->set('flash', $this->container->get('flash'))
            ->render();
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

        return new ResponseRedirect($this->router->make('main'));
    }

    /**
     * Displays controllers and bootstrap source
     *
     * @return Response
     */
    public function sourceAction()
    {
        $path = $this->container->get('path.app') . '/Moss/Sample';
        $content = $this->container->get('view')
            ->template('Moss:Sample:source')
            ->set('method', __METHOD__)
            ->set('controller', highlight_file($path . '/controller/SampleController.php.', 1))
            ->set('bootstrap', highlight_file($path . '/bootstrap.php', 1))
            ->render();

        return new Response($content);
    }
}
<?php
namespace Moss\Sample\Controller;

use Moss\Http\Response\Response;
use Moss\Http\Response\ResponseRedirect;
use Moss\Kernel\App;
use Moss\Security\AuthenticationException;

/**
 * Class SampleController
 *
 * @package Moss\Sample
 */
class SampleController
{
    protected $app;


    /**
     * Constructor
     *
     * @param App $moss
     */
    public function __construct(App $moss)
    {
        $this->app = & $moss;
    }

    /**
     * Sample method, displays link to controller source
     *
     * @return Response
     */
    public function indexAction()
    {
        $content = $this->app->get('view')
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
            if (!$this->app->request->method('post')) {
                throw new AuthenticationException('Unable to authenticate, invalid method');
            }

            $this->app->get('security')
                ->tokenize($this->app->request->body->all());

            return new ResponseRedirect($this->app->router->make('source'));
        } catch (AuthenticationException $e) {
            $this->app->get('flash')
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
        return $this->app->get('view')
            ->template('Moss:Sample:login')
            ->set('method', __METHOD__)
            ->set('flash', $this->app->get('flash'))
            ->render();
    }

    /**
     * Logout
     *
     * @return ResponseRedirect
     */
    public function logoutAction()
    {
        $this->app->get('security')
            ->destroy();

        return new ResponseRedirect($this->app->router->make('main'));
    }

    /**
     * Displays controllers and bootstrap source
     *
     * @return Response
     */
    public function sourceAction()
    {
        $path = $this->app->get('path.app') . '/Moss/Sample';
        $content = $this->app->get('view')
            ->template('Moss:Sample:source')
            ->set('method', __METHOD__)
            ->set('controller', highlight_file($path . '/controller/SampleController.php.', 1))
            ->set('bootstrap', highlight_file($path . '/bootstrap.php', 1))
            ->render();

        return new Response($content);
    }
}
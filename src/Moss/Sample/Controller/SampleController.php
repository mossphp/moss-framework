<?php
namespace Moss\Sample\Controller;

use Moss\Container\Container;
use Moss\Http\Request\Request;
use Moss\Http\Response\Response;
use Moss\Http\Response\ResponseRedirect;
use Moss\Http\Router\Router;
use Moss\Moss;
use Moss\Security\AuthenticationException;

/**
 * Class SampleController
 *
 * @package Moss\Sample
 */
class SampleController
{
    protected $moss;


    /**
     * Constructor
     *
     * @param Moss $moss
     */
    public function __construct(Moss $moss)
    {
        $this->moss = & $moss;
    }

    /**
     * Sample method, displays link to controller source
     *
     * @return Response
     */
    public function indexAction()
    {
        $content = $this->moss->get('view')
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
            if (!$this->moss->request->method('post')) {
                throw new AuthenticationException('Unable to authenticate, invalid method');
            }

            $this->moss->get('security')
                ->tokenize($this->moss->request->body->all());

            return new ResponseRedirect($this->moss->router->make('source'));
        } catch (AuthenticationException $e) {
            $this->moss->get('flash')
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
        return $this->moss->get('view')
            ->template('Moss:Sample:login')
            ->set('method', __METHOD__)
            ->set('flash', $this->moss->get('flash'))
            ->render();
    }

    /**
     * Logout
     *
     * @return ResponseRedirect
     */
    public function logoutAction()
    {
        $this->moss->get('security')
            ->destroy();

        return new ResponseRedirect($this->moss->router->make('main'));
    }

    /**
     * Displays controllers and bootstrap source
     *
     * @return Response
     */
    public function sourceAction()
    {
        $path = $this->moss->get('path.app') . '/Moss/Sample';
        $content = $this->moss->get('view')
            ->template('Moss:Sample:source')
            ->set('method', __METHOD__)
            ->set('controller', highlight_file($path . '/controller/SampleController.php.', 1))
            ->set('bootstrap', highlight_file($path . '/bootstrap.php', 1))
            ->render();

        return new Response($content);
    }
}
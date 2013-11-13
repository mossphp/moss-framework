<?php
namespace moss\sample\controller;

use moss\container\ContainerInterface;
use moss\http\response\Response;

class SampleController
{

    /**
     * Constructor, calls init function
     *
     * @param ContainerInterface $Container
     */
    public function __construct(ContainerInterface $Container)
    {
        $this->Container = & $Container;
    }

    /**
     * Sample method, displays hello text
     */
    public function indexAction()
    {
        return new Response(
            sprintf(
                'Hello, this is sample controller. <a href="%s">Go to documentation</a>',
                $this->Container
                    ->get('router')
                    ->make('moss:autodoc:Autodoc:index')
            )
        );
    }
}
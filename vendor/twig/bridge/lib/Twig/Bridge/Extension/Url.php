<?php
class Twig_Bridge_Extension_Url extends \Twig_Extension
{

    protected $router;

    public function __construct(\Moss\http\router\Router $router)
    {
        $this->router = & $router;
    }

    public function getFunctions()
    {
        return array(
            'url' => new \Twig_Function_Method($this, 'url'),
        );
    }

    public function url($identifier = null, $arguments = array())
    {
        return $this->router->make($identifier, $arguments);
    }

    public function getName()
    {
        return 'url';
    }
}
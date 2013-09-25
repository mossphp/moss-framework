<?php
class Twig_Bridge_Extension_Url extends \Twig_Extension
{

    protected $Router;

    public function __construct(\moss\router\Router $Router)
    {
        $this->Router = & $Router;
    }

    public function getFunctions()
    {
        return array(
            'Url' => new \Twig_Function_Method($this, 'Url'),
        );
    }

    public function Url($identifier = null, $arguments = array())
    {
        return $this->Router->make($identifier, $arguments);
    }

    public function getName()
    {
        return 'Url';
    }
}
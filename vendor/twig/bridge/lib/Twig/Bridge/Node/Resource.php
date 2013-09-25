<?php

class Twig_Bridge_Node_Resource extends Twig_Node implements Twig_NodeOutputInterface
{

    public function __construct(Twig_Node_Expression $resource, $lineno, $tag = null)
    {
        parent::__construct(array('resource' => $resource), array(), $lineno, $tag);
    }

    public function compile(Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        $compiler
            ->write('echo $this->env->getExtension(\'Resource\')->build(')
            ->subcompile($this->getNode('resource'))
            ->raw(");\n");
    }
}

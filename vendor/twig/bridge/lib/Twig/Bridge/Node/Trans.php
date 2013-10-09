<?php
class Twig_Bridge_Node_Trans extends \Twig_Node
{
    public function __construct(\Twig_NodeInterface $body, \Twig_Node_Expression $count = null, \Twig_Node_Expression $vars = null, \Twig_Node_Expression $locale = null, $lineno = 0, $tag = null)
    {
        parent::__construct(array('count' => $count, 'body' => $body, 'vars' => $vars, 'locale' => $locale), array(), $lineno, $tag);
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $vars = $this->getNode('vars');
        $defaults = new \Twig_Node_Expression_Array(array(), -1);
        if ($vars instanceof \Twig_Node_Expression_Array) {
            $defaults = $this->getNode('vars');
            $vars = null;
        }

        list($msg, $defaults) = $this->compileString($this->getNode('body'), $defaults);

        $method = null === $this->getNode('count') ? 'trans' : 'transChoice';

        $compiler
            ->write('echo $this->env->getExtension(\'translator\')->' . $method . '(')
            ->subcompile($msg);

        $compiler->raw(', ');

        if ($this->getNode('count') !== null) {
            $compiler
                ->subcompile($this->getNode('count'))
                ->raw(', ');
        }

        if ($vars !== null) {
            $compiler
                ->raw('array_merge(')
                ->subcompile($defaults)
                ->raw(', ')
                ->subcompile($this->getNode('vars'))
                ->raw(')');
        } else {
            $compiler->subcompile($defaults);
        }

        if ($this->getNode('locale') !== null) {
            $compiler->raw(', ');
            $compiler
                ->raw(', ')
                ->subcompile($this->getNode('locale'));
        }

        $compiler->raw(");\n");
    }

    protected function compileString(\Twig_NodeInterface $body, \Twig_Node_Expression_Array $vars)
    {
        if ($body instanceof \Twig_Node_Expression_Constant) {
            $msg = $body->getAttribute('value');
        } elseif ($body instanceof \Twig_Node_Text) {
            $msg = $body->getAttribute('data');
        } else {
            return array($body, $vars);
        }

        preg_match_all('/(?<!%)%([^%]+)%/', $msg, $matches);

        if (version_compare(\Twig_Environment::VERSION, '1.5', '>=')) {
            foreach ($matches[1] as $var) {
                $key = new \Twig_Node_Expression_Constant('%' . $var . '%', $body->getLine());
                if (!$vars->hasElement($key)) {
                    $vars->addElement(new \Twig_Node_Expression_Name($var, $body->getLine()), $key);
                }
            }
        } else {
            $current = array();
            foreach ($vars as $name => $var) {
                $current[$name] = true;
            }
            foreach ($matches[1] as $var) {
                if (!isset($current['%' . $var . '%'])) {
                    $vars->setNode('%' . $var . '%', new \Twig_Node_Expression_Name($var, $body->getLine()));
                }
            }
        }

        return array(new \Twig_Node_Expression_Constant(str_replace('%%', '%', trim($msg)), $body->getLine()), $vars);
    }
}

<?php
class Twig_Bridge_TokenParser_Trans extends Twig_TokenParser
{
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $vars = new \Twig_Node_Expression_Array(array(), $lineno);
        $body = null;
        $locale = null;
        if (!$stream->test(\Twig_Token::BLOCK_END_TYPE)) {
            if ($stream->test('with')) {
                // {% trans with vars %}
                $stream->next();
                $vars = $this->parser
                    ->getExpressionParser()
                    ->parseExpression();
            }

            if ($stream->test('into')) {
                // {% trans into "fr" %}
                $stream->next();
                $locale = $this->parser
                    ->getExpressionParser()
                    ->parseExpression();
            }

            if(!$stream->test(Twig_Token::BLOCK_END_TYPE)) {
                $body = $this->parser
                    ->getExpressionParser()
                    ->parseExpression();
            }
//                throw new \Twig_Error_Syntax('Unexpected token. Twig was looking for the "with" keyword.');
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        // {% trans "message" %}
        if($body) {
            $this->assertBody($body);
            return new Twig_Bridge_Node_Trans($body, null, $vars, $locale, $lineno, $this->getTag());
        }

        // {% trans %}message{% endtrans %}

        $body = $this->parser->subparse(array($this, 'decideTransFork'), true);

        $this->assertBody($body);

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new Twig_Bridge_Node_Trans($body, null, $vars, $locale, $lineno, $this->getTag());
    }

    public function assertBody($body) {
        if (!$body instanceof \Twig_Node_Text && !$body instanceof \Twig_Node_Expression) {
            throw new \Twig_Error_Syntax('A message inside a trans tag must be a simple text');
        }
    }

    public function decideTransFork($token)
    {
        return $token->test(array('endtrans'));
    }

    public function getTag()
    {
        return 'trans';
    }
}

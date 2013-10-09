<?php
class Twig_Bridge_TokenParser_TransChoice extends Twig_Bridge_TokenParser_Trans
{
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $vars = new \Twig_Node_Expression_Array(array(), $lineno);

        $count = $this->parser
            ->getExpressionParser()
            ->parseExpression();

        $domain = null;
        $locale = null;

        if ($stream->test('with')) {
            // {% transchoice count with vars %}
            $stream->next();
            $vars = $this->parser
                ->getExpressionParser()
                ->parseExpression();
        }

        if ($stream->test('into')) {
            // {% transchoice count into "fr" %}
            $stream->next();
            $locale = $this->parser
                ->getExpressionParser()
                ->parseExpression();
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse(array($this, 'decideTransChoiceFork'), true);

        $this->assertBody($body);

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new Twig_Bridge_Node_Trans($body, $count, $vars, $locale, $lineno, $this->getTag());
    }

    public function decideTransChoiceFork($token)
    {
        return $token->test(array('endtranschoice'));
    }

    public function getTag()
    {
        return 'transchoice';
    }
}

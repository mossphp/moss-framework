<?php
class Twig_Bridge_TokenParser_Resource extends Twig_TokenParser
{

    public function parse(Twig_Token $token)
    {
        $resource = $this->parser
            ->getExpressionParser()
            ->parseExpression();
        $this->parser
            ->getStream()
            ->expect(Twig_Token::BLOCK_END_TYPE);

        return new Twig_Bridge_Node_Resource($resource, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'resource';
    }
}

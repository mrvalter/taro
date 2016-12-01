<?php
namespace Kernel\Services\Viewer\Extensions;
use Kernel\Services\Router;

class WidgetTokenParser extends \Twig_TokenParser{
	
	public function parse(\Twig_Token $token)
    {
		
        $parser = $this->parser;
        $stream = $parser->getStream();
		$name = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
		
		//$value = $token->getValue();
		
		
		if ($stream->nextIf(\Twig_Token::OPERATOR_TYPE, '=')) {
            $values = $this->parser->getExpressionParser()->parseMultitargetExpression();			
			$value = $values->getNode(0)->getAttribute('value');
			$stream->expect(\Twig_Token::BLOCK_END_TYPE);				
        }
		
		$val = (string)Router::createFromUrl($value)->execute();
		return new \Twig_Node_Text($val, $token->getLine());
    }

    public function getTag()
    {
        return 'widget';
    }
	
}

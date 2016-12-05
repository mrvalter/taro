<?php
namespace Kernel\Services\Viewer\Extensions;
use Kernel\Services\Router;

class WidgetTokenParser extends \Twig_TokenParser{
	
	private $env;
	
	public function __construct($env) {
		$this->env = $env;
	}
	
	public function parse(\Twig_Token $token)
    {
		
		$parser = $this->parser;
        $stream = $parser->getStream();

        $name = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
        $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
        $value = $parser->getExpressionParser()->parseExpression();		
		
		if($nameToken = $stream->nextIf(\Twig_Token::NAME_TYPE)){
			$name = $nameToken->getValue();
			$stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
			$value = $parser->getExpressionParser()->parseExpression();
			
			if(!$value instanceof \Twig_Node_Expression_Array){
				throw new \Twig_Error_Syntax("params of widget must be array ", $stream->getCurrent()->getLine(), $stream->getSourceContext()->getName());
			}
			
			
			$resParams = [];
			
				foreach($value->getKeyValuePairs() as $av){	
					
					if($av['value'] instanceof \Twig_Node_Expression_Constant){						
						$resParams[] = $av['value']->getAttribute('value');
					}else{
						$resParams[] = '';
						var_dump($av);
					}
				}
			
			var_dump($name, $resParams);
		}
		
		$stream->expect(\Twig_Token::BLOCK_END_TYPE);				
		die();
        
        
    }

    public function getTag()
    {
        return 'widget';
    }
	
	public function ttt()
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
	
}



<?php

namespace Kernel\Services;

class ReflectionChecker {
	
	const CLASS_NOT_FOUND = 1;
	const METHOD_NOT_FOUND = 2;
	const MORE_PARAMETERS_SEND=3;
	const REQUIRED_PARAM_NOT_SET=4;
	const TYPE_MISMATCH = 5;
	
	public static function checkClass(string $className, string $methodName='__construct', array $params=[], $throwMoreParams=true) {				
		
		if(!class_exists($className)){
			throw new \ReflectionCheckerException ("Не найден класс  {$className}", "", self::CLASS_NOT_FOUND);
		}
		
		$refClass = new \ReflectionClass($className);
		if(!$refClass->hasMethod($methodName)){
			throw new \ReflectionCheckerException ("Не найден метод {$className}::{$methodName}", "", self::METHOD_NOT_FOUND);
		}
		
		$refMethod = $refClass->getMethod($methodName);
		$refParameters = $refMethod->getParameters();
				
		if(count($params) > count($refParameters) && $throwMoreParams){
			throw new \ReflectionCheckerException (
					"Передано больше параметров,"
					. " чем прописано в методе $className::$methodName", 
					"", self::MORE_PARAMETERS_SEND);
		}
		
		if(isset($refParameters[0])){
			foreach($refParameters as $i=>$refParameter){
				$pType = $refParameter->hasType() ? (string)$refParameter->getType() : null;
				
				if(!$refParameter->isDefaultValueAvailable() && !isset($params[$i])){					
					throw new \ReflectionCheckerException ('Не передан обязательный параметр '
							. (null !== $pType? " {$pType} " : "").$refParameter->getName()
							. " в метод {$className}::{$methodName}", '', self::REQUIRED_PARAM_NOT_SET);
				}
				
				if(null !== $pType){
					switch($pType){
						case 'int':
							if(isset($params[$i]) && !is_numeric($params[$i])){
								throw new \ReflectionCheckerException('Переданный параметр не соответствует типу', '', self::TYPE_MISMATCH);
							}
							break;

					}
				}				
			}
		}				
	}
	
	
}

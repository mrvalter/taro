<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services\Filters;
/**
 * @category MED CRM
 */
class Rustext implements \Services\Interfaces\FilterModifier{
	
	public function execute(&$value)
	{		
		$result = replaceEngToRusChars($value);				
		return trim($result);
	}
}

<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services\Filters;
/**
 * @category MED CRM
 */
class Boolean implements \Services\Interfaces\FilterModifier{
	
	public function execute(&$value)
	{		
		return (bool)$value;
	}
}

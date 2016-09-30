<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services\Filters;
/**
 * @category MED CRM
 */
class Required implements \Services\Interfaces\FilterChecker
{
    public function execute(&$value) {
		if(is_string($value)){
			$value = trim($value);
		}		
        return $value? true : false;
    }
    
    public function getError()
    {
        return 'Поле обязательно для заполнения';
    }
}

<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services\Filters;
/**
 * @category MED CRM
 */
class Mobilephone implements \Services\Interfaces\FilterChecker
{
    public function execute(&$value) {
        if(!$value){
			return false;
		}
		return '';
    }
    
    public function getError()
    {
        return 'Мобильный номер не соответствует стандарту';
    }
}

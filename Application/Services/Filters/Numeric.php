<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services\Filters;
/**
 * @category MED CRM
 */
class Numeric implements \Services\Interfaces\FilterChecker
{
    public function execute(&$value) {
               
        if(trim($value) == ''){
            $value = 0;
        }
        return is_numeric($value)? true : false;
    }
    
    public function getError()
    {
        return 'Поле должно быть номером';
    }
}

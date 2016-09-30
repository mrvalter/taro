<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services\Filters;
/**
 * @category MED CRM
 */
class Mail implements \Services\Interfaces\FilterChecker
{
    public function execute(&$value) {
        return '';
    }
    
    public function getError()
    {
        return 'Поле обязательно для заполнения';
    }
}

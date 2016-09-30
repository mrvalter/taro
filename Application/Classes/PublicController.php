<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Classes;
/**
 * @category MED CRM
 */
class PublicController extends Controller{
    
    public function __construct(\Services\Interfaces\ViewInterface $view) {
		parent::__construct($view);
    }
		
}

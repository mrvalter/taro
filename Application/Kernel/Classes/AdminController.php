<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Kernel\Classes;
/**
 * @category MED CRM
 */
class AdminController extends Controller{
    //put your code here
    public function __construct(\Services\Interfaces\ViewInterface $view) {
        
		parent::__construct($view);
        $this->setLayout('admin.layout');	
    }
		
}

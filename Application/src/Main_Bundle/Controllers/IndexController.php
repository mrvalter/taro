<?php
namespace Main_Bundle\Controllers;

use Kernel\Classes\Controller;

class IndexController extends Controller {
    
    public function indexAction()
    {					
		
		return $this->render('main_page');
    }	
}

<?php
namespace MedCrm_Bundle\Controllers;
use Kernel\Classes\Controller;

class IndexController extends Controller{
	
	/* главная страница medcrm*/
	public function indexAction()
	{
		
		return $this->render('main_page');
	}
}

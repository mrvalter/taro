<?php
namespace Game_Bundle\Controllers;

use Game_Bundle\Classes\TestRepository;
use Kernel\Classes\Controller;




class IndexController extends Controller{
	
	
	public function indexAction()
	{
		
		return $this->render('main-window');
	}
}

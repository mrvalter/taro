<?php

namespace Widgets_Bundle\Controllers;
use Kernel\Classes\Controller;

class MenuController extends Controller{
	
	public function leftMenuAction()
	{
		$menu = $this->getService('menu_builder')->getMenuCollection();		
		return $this->render('left_menu', ['menu' => $menu, 'sok'=>'pok']);
	}
}

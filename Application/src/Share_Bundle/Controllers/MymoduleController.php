<?php
namespace Share_Bundle\Controllers;

use Kernel\Classes\ModuleController;

class MymoduleController extends ModuleController {
	
	public function indexAction(string $action, int $id)
	{
		
		var_dump(__CLASS__);
		die();
	}
	
}

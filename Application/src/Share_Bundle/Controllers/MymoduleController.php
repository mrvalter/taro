<?php
namespace Share_Bundle\Controllers;

use Kernel\Classes\ModuleController;
use Classes\User;

class MymoduleController extends ModuleController {
	
	public function indexAction(string $action='', int $id=0, User $user=null, ...$others)
	{
		var_dump($action);
		var_dump($id);
		
		die();
	}
	
}

<?php
namespace Firewall_Bundle\Controllers;

use Kernel\Classes\Controller;
/**
 * Description of MessageController
 *
 * @author sworion
 */
class MessageController extends Controller{
	
	public function notFoundAction()
	{
		return $this->render('page_not_found');
	}
}

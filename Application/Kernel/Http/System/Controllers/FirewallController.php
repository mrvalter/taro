<?php
namespace Kernel\System\Http\Controllers;

use \Kernel\Classes\Controller;
/**
 * Description of FirewallController
 *
 * @author sworion
 */
class FirewallController extends Controller {
	
	public function accessDeniedAction()
	{
		return $this->render('access_denied');
	}
	
	public function pageNotFoundAction()
	{
		return $this->render('page_not_found');
	}
	
}

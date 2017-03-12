<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Main_Bundle\Controllers;
use Kernel\Classes\Controller;

/**
 * Description of FirewallController
 *
 * @author sworion
 */
class FirewallController extends Controller{
	
	public function authorizeAction()
	{
		$canRegistration = $this->getService('firewall')->canRegistration();	
		var_dump($this->render('login_form', ['canRegistration'=>$canRegistration]));		
		die();
	}
	
	public function registrationAction()
	{
		
	}
	
}

<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services;
/**
 * @category MED CRM
 */
class RedirectManager {
	
	private $redirectConfig = [];
	
	public function __construct(Config $config) {
		$this->redirectConfig = $config->get('redirect');		
	}
	
	public function getRedirectFromRoute($path)
	{
		foreach($this->redirectConfig as $pattern=>$controllerArr){
			if(preg_match($pattern, $path)){
				return $controllerArr;
			}
		}
		
		return null;
	}
}

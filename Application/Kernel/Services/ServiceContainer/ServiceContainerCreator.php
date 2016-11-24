<?php

namespace Kernel\Services\ServiceContainer;

use Kernel\Services\Config;


/**
 * Description of sCont
 *
 * @author sworion
 */
class ServiceContainerCreator {		
	
	private $config;
	
	public function __construct(Config $config)
	{
		$this->config = $config;
		
	}
	
	public function createServicesClass()
	{
		$services = $this->config->getValue();
		var_dump($services);
		
		if(!sizeof($services)){
			return;
		}
		
		\Twig_Autoloader::register();
		$loader = new \Twig_Loader_Filesystem(__DIR__);		
		$twig = new \Twig_Environment($loader);
		$phpfile = $twig->render('service_container.twig', [
			'services'=>$services
		]);
			
		var_dump($phpfile);
				
	}
}

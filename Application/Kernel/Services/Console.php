<?php

namespace Kernel\Services;
use Kernel\Services\FileDataStorage;


class Console {
	
	private $serviceContainer;
	private $httpPath;
	private $options='';
	private $longoptions=[
		'asserts:'
	];
	
	private $params=[];
	
	private $bundlesMediaPath = '';
	
	public function __construct(\ServiceContainer $serviceContainer, $httpPath)
	{
		$this->httpPath = $httpPath;
		$this->bundlesMediaPath = $this->httpPath.'/media/bundles';
		$this->serviceContainer = $serviceContainer;
		$this->params = getopt($this->options, $this->longoptions);
	}
	
	public function run()
	{
		if(isset($this->params['asserts'])){
			$this->asserts();
		}		
	}
	
	private function asserts()
	{
		$params = $this->params['asserts'];
		$params = getopt('',['asserts:']);
		if(isset($params['asserts'])){			
			switch($params['asserts']){
				case 'refresh':
					FileDataStorage::removeDirectory($this->bundlesMediaPath);
					FileDataStorage::makeDir($this->bundlesMediaPath);
					$firewall = $this->serviceContainer->get('firewall');
					$bundles = $firewall->getBundles();					
					if(!empty($bundles)){
						foreach($bundles as $bname=>$bundle){
							$path = $firewall->getPathToBundle($bundle);							
							if(null === $path || !file_exists($path.'/media')){
								continue;
							}							
							shell_exec('cd '.$this->httpPath.'/media/bundles; ln -s '.$path.'/media ./'.$bname);
							$this->printC("Asserts $bname is created");
						}
					}
					
					break;
			}
		}
	}
	
	private function printC($string)
	{
		echo $string."\r\n";
	}
		
}

<?php

namespace Kernel\Services\ServiceContainer;

use Kernel\Services\Config;
use Kernel\Classes\Types\ObjectsCollection;


/**
 * Description of sCont
 *
 * @author sworion
 */
class ServiceContainerCreator {		
	
	private $services;
	
	public function __construct(Config $config)
	{
		$this->services = $config->getValue();		
	}
	
	public function createServicesClass()
	{		
		$classes = [];
		foreach($this->services as $name=>&$service){
			
			$classes[] = $service['class'];
			$classParts = explode('\\', $service['class']);			
			$service['_shortNameClass'] = array_pop($classParts);
						
			$log = $name=="test1" ? true : false;
			
			/* Проверяем нет ли зацикливания сервисов */
			$namesUsed = [];
			if(isset($service['params']) && sizeof($service['params'])){
				$namesUsed[] = $name;
				$check = $this->checkService($service['params'], $namesUsed, $log);
				
				if(true !== $check){
					var_dump($this->services);
					var_dump('Обнаружено зацикливание сервисов');
					var_dump($check);					
					exit;
				}
			}										
		}
		
		$classes = array_unique($classes);
	
		var_dump($this->services);
		
		\Twig_Autoloader::register();
		$loader = new \Twig_Loader_Filesystem(__DIR__);		
		$twig = new \Twig_Environment($loader);
		$phpfile = $twig->render('service_container.twig', [
			'services' => $this->services,
			'classes'  => $classes
		]);
			
		var_dump($phpfile);
				
	}
	
	/** @TODO CHECK SERVICES TEST */
	public function checkService(array $params=[], array $namesUsed = [], bool $log=false)
	{
		if(empty($params)){
			return true;
		}				
		
		$nowServiceName = array_pop($namesUsed);
		$nowService = $this->services[$nowServiceName] ?? null;
		$parentServiceName = isset($namesUsed[1]) ? $namesUsed[0] : null;
		
		if(null === $nowService){			
			throw new \ServiceException("Не найден сервис $serviceName". 
					(null !== $parentServiceName ? ", указанный в $parentServiceName !" : ' !'));
		}
		
		if(!isset($nowService['class']) || !trim($nowService['class'])){
			throw new \ServiceException ("Не определен класс в сервисе $nowServiceName");
		}
		
		if(!class_exists($nowService['class'])){
			throw new \ServiceException ("Не найден класс  {$nowService['class']}, объявленный в сервисе $nowServiceName");
		}
		
				
		foreach($params as $param){

			if(is_string($param) && substr($param, 0, 1) != "@"){
				continue;
			}
			
			$serviceName = substr($param, 1);
			$chain = array_merge($namesUsed, [$serviceName]);
			
			if(!isset($this->services[$serviceName])){
				throw new \ServiceException("Не найден сервис $serviceName, указанный в ".array_pop($namesUsed));
			}
			
			if(in_array($serviceName, $namesUsed)){
				return $chain;
			}
						
			$nextParams  = $this->services[$serviceName]['params'] ?? [];			
			
			if(empty($nextParams)){
				return true;
			}
			
			$check = $this->checkService($nextParams, $chain, $log);
			
			if(true !== $check){
				return $check;
			}			
		}
		
		return true;
		
	}
}

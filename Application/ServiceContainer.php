<?php

use Kernel\Services\Config;
use Kernel\Interfaces\ServiceContainerInterface;

class ServiceContainer implements ServiceContainerInterface{
    
	/** @var array */
    private $services;
	
	/** @var Config */
    private $config;  
    
    /**
     * @param Config $config 
     */
    public function __construct(Config $config = null)
    {
        $this->config = $config ?? new Config();
    }
    
    final public function addService(string $name, $object): self
    {     
				
        if(!is_object($object)){
            throw new ServiceException(' Сервис "'.$name.'" должен быть объектом ');
        }
        $this->services[$name] = $object;
		return $this;
    }
    
    /**
     * Загружает и возвращает объект сервиса
     * @throws ServiceException
     * @param type $name Имя сервиса
     * @return object  Возвращает объект сервиса
     */
    final public function get(string $name)
    {		                		
		return $this->services[$name] ?? $this->initService($name);
    }	
    
    /**
     * Загружает нужный сервис и сервисы от которых он зависит.
     * Если сервисы ссылаются друг на друга выдаст исключение.
     * 
     * @param type $name
     * @param array $services Имена вызывающих сервисов
     * @throws \ServiceException
     */
    private function initService(string $serviceName, array $usedNames=[])
    {		
        if(isset($this->services[$serviceName])){
            return $this->services[$serviceName];
        }                						
       
		if(in_array($serviceName, $usedNames)){
			throw new \ServiceCycleException('Обнаружена цикличность сервисов '.implode('=>',$usedNames)."=>$serviceName");
		}        		
		
        $class = $this->config->getValue($serviceName, 'class') ?? '';
        $params = $this->config->getValue($serviceName, 'params') ?? [];
        		
		if(!$class){
			throw new \ServiceNotFoundException('Не найден сервис "'.$serviceName.'" в конфиге приложения');
		}
		
        if(empty($params)){
            return $this->services[$serviceName] = new $class();
        }        						
			
        /* Загружаем все нужные сервисы */
		$refService = new \ReflectionClass($class);		
		$refConstructor = $refService->getConstructor();
		$refParameters = $refConstructor->getParameters();						
		
		
		/* Подключаем нужные сервисы */
		foreach ($params as &$pValue){
			if(is_string($pValue) && substr($pValue, 0, 1) == "@"){
				$addserviceName = substr($pValue, 1);
				$chain = array_merge($usedNames, [$serviceName]);
				$pValue = $this->services[$addserviceName] = $this->initService($addserviceName, $chain);
			}
		}				
		
		$resParams = [];
		foreach ($refParameters as $i => $refParameter){			
			if($refParameter->isVariadic()){
				var_dump($params);
				$resParams = array_merge($resParams, $params);
				break;
			}
			$pname = $refParameter->getName();
			if(isset($params[$i])){
				$resParams[] = $params[$i];
				unset($params[$i]);
			}elseif(isset($params[$pname])){
				$resParams[] = $params[$pname];
				unset($params[$pname]);
			}elseif($refParameter->isDefaultValueAvailable ()){
				$resParams[] = $refParameter->getDefaultValue();
			}else{
				throw new \InvalidArgumentException("Не найден параметр $pname в конфиге сервиса $serviceName");
			}									
		}
        		
        return $this->services[$serviceName] = $refService->newInstanceArgs($resParams);
        
    }		
}

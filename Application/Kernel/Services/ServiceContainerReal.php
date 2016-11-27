<?php
namespace Kernel\Services;

use Kernel\Services\Config;

class ServiceContainerReal {
    
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
    
    public function addService($name, $object)
    {     
				
        if(!is_object($object)){
            throw new ServiceException(' Сервис "'.$name.'" должен быть объектом ');
        }
        $this->services[$name] = $object;
    }
    
    /**
     * Загружает и возвращает объект сервиса
     * @throws ServiceException
     * @param type $name Имя сервиса
     * @return object  Возвращает объект сервиса
     */
    public function get($name)
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
    private function initService(string $name, array $usedNames=[])
    {		
        if(isset($this->services[$name])){
            return $this->services[$name];
        }                						
       
		if(in_array($name, $usedNames)){
			throw new \ServiceCycleException('Обнаружена цикличность сервисов '.implode('=>',$usedNames)."=>$name");
		}        		
		
        $class = $this->config->getValue($name, 'class') ?? '';
        $params = $this->config->getValue($name, 'params') ?? [];
        
		if(!$class){
			throw new \ServiceNotFoundException('Не найден сервис "'.$name.'" в конфиге приложения');
		}
		
        if(!isset($params[0])){
            return $this->services[$name] = new $class();
        }
        
		
        /* Загружаем все нужные сервисы */
        foreach($params as $i=>$param){			
            if(is_string($param) && substr($param, 0, 1) == "@"){               
                $serviceName = substr($param, 1);
				$chain = array_merge($usedNames, [$name]);
                $this->services[$param] = $resParams[$i] = $this->initService($serviceName, $chain);
            }else{
                $resParams[$i] = $param;
            }            
        }                
         		
        return $this->services[$name] = new $class(...$resParams);
        
    }
}

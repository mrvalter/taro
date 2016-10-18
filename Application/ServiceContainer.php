<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */

/**
 * @category MED CRM
 */
class ServiceContainer {
    
    private $services;
    private $config;    
    
    /**
     * @param Config $config 
     * 
     */
    public function __construct(array $config=[])
    {
        $this->config = $config;
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
        if(!sizeof($this->config)){
            throw new ServiceException('Не загружено ни одного сервиса в конфигурационный файл');
        }
        		
        if(isset($this->services[$name])){            
            return $this->services[$name];
        }
        
		return $this->initService($name);
						
    }	
    
    /**
     * Загружает нужный сервис и сервисы от которых он зависит.
     * Если сервисы ссылаются друг на друга выдаст исключение.
     * 
     * @param type $name
     * @param array $services Имена вызывающих сервисов
     * @throws \ServiceException
     */
    private function initService($name, &$services=array())
    {      
						
        if(isset($this->services[$name])){
            return $this->services[$name];
        }
        
        if(!isset($this->config[$name])){
            throw new \ServiceException('Не найдено сервиса "'.$name.'" в конфигурационном файле');
        }
             
        
        if(!sizeof($services)){
            $key = array_search($name, $services);
            if(false !== $key){
                throw new \ServiceException('Сервисы "'.$name.'" и "'.$services[$key].'" ссылаются друг на друга');
            }
        }
        
        $class = isset($this->config[$name]['class']) ? $this->config[$name]['class'] : '';
        $params = isset($this->config[$name]['params']) ? $this->config[$name]['params'] : array();
        
		if(!$class){
			throw new \ServiceException('Не найден сервис "'.$name.'" в конфиге приложения');
		}
		
        if(!sizeof($params)){
            return $this->services[$name] = new $class();             
        }
                
        /* Загружаем все нужные сервисы */
        foreach($params as $paramName=>$param){            
            if(!is_array($param) && 0 === strpos($param, '@')){                
                $param = substr($param, 1);
                $service[] = $param;
                $this->services[$param] = $resParams[$paramName] = $this->initService($param, $services);                
            }else{
                $resParams[$paramName] = $param;
            }
            $services = array();
        }
        
        $ref = new \ReflectionClass($class);
        $constructor = $ref->getConstructor();
        
        if(!$constructor instanceof \ReflectionMethod){
            return $this->services[$name] = $ref->newInstanceArgs();
        }
        $refParameters = $constructor->getParameters();
        $arguments = array();        
        if(sizeof($refParameters)){            
            foreach($refParameters as $i=>$refParameter){
                $value = null;
                if(isset($resParams[$refParameter->name])){
                    $value = $resParams[$refParameter->name];
                }elseif(isset($resParams[$i])){
                    $value = $resParams[$i];
                }
                                
                $arguments[] = $value;
            }
        }
                
        return $this->services[$name] = $ref->newInstanceArgs($arguments);
        
    }
}

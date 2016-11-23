<?php
namespace Kernel;

use Kernel\Services\Config;

class ServiceContainer {
    
	/** @var array */
    private $services;
	
	/** @var Config */
    private $config;    
    
    /**
     * @param Config $config 
     */
    public function __construct(Config $config = null)
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
    private function initService(string $name, array &$services = [])
    {		
        if(isset($this->services[$name])){
            return $this->services[$name];
        }
        
        if(null === $this->config>getValue($name)){
            throw new \ServiceException('Не найдено сервиса "'.$name.'" в конфигурационном файле');
        }
		
                
        if(isset($services[0])){
            $key = array_search($name, $services);
            if(false !== $key){
                throw new \ServiceException('Сервисы "'.$name.'" и "'.$services[$key].'" ссылаются друг на друга');
            }
        }
        
        $class = $this->config->getValue($name, 'class') ?? '';
        $params = $this->config->getValue($name, 'params') ?? [];
        
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

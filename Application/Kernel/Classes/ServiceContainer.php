<?php
namespace Kernel\Classes;
use Classes\UserRepository;

use Kernel\Interfaces\ConfigInterface;
use Kernel\Interfaces\ServiceContainerInterface;
/* Сервисы */
use Kernel\Services\Security\SessionStorage\NativeSessionStorage;
use Kernel\Services\MenuBuilder_MM\MenuBuilder;



/**
 * @property-read  MenuBuilder $menu_builder Постройщик меню
 * @property-read NativeSessionStorage $session_storage Сессия
 */
abstract class ServiceContainer implements ServiceContainerInterface{
    
	/** @var array */
    private $services;
	
	private $staticServices = [];
	
	/** @var Config */
    private $config;  
    
    /**
     * @param Config $config 
     */
    public function __construct(ConfigInterface $config)
    {
		
        $this->config = $config;
    }
    
    final public function addService(string $name, $object, bool $static = false): self
    {     
				
        if(!is_object($object)){
            throw new ServiceException(' Сервис "'.$name.'" должен быть объектом ');
        }
		if(isset($this->services[$name]) && isset($this->staticServices[$name])) {
			throw new ServiceException(' Сервис "'.$name.'" уже инициализирован ');
		}
		
        $this->services[$name] = $object;
		
		if($static){
			$this->staticServices[$name] = 1;
		}
		return $this;
    }    	
	
    /**
     * Загружает и возвращает объект сервиса
     * @throws ServiceException
     * @param type $name Имя сервиса
     * @return object  Возвращает объект сервиса
     */
    final protected function get(string $name)
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
       var_dump($serviceName);
		if(in_array($serviceName, $usedNames)){
			throw new \ServiceCycleException('Обнаружена цикличность сервисов '.implode('=>',$usedNames)."=>$serviceName");
		}        		
		
		if(isset($this->services[$serviceName])){
            return $this->services[$serviceName];
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

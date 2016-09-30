<?php
namespace Classes;
/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
use Classes\Image as Image;

/**
 * @category MED CRM
 */
trait Getter {
    
    function __get($name) {
		
		$safety = false;
		
		if(substr($name, 0,1) == '*'){
			$name = substr($name, 1);
			$safety = true;
		}
				
        if(substr($name, 0,1) != '_'){
			
			if(method_exists($this, 'get'.  $name)){
				return call_user_func_array(array($this, 'get'.  $name), []);
			}elseif(property_exists($this, $name)){
				if($this->$name instanceof \Closure){
					return $this->$name = $this->$name->__invoke();
				}
                return $safety? htmlspecialchars($this->$name) : $this->$name;
            }
        }            
    } 
	
	public function isExists()
	{
		return is_numeric($this->id) && $this->id > 0;
	}
	
	
	/**
	 * Конвертирует объект в нужный тип
	 * 
	 * @param string $format  json|array
	 * @param boolean $imageB64convert Конвертировать ли картинки в b64
	 * @return mixed
	 */
	public function convertTo($format)
	{
		switch($format){
			case 'json':
				return $this->toJSON();
			case 'array':
				return $this->toArray();
				
		}
		
		return $this;		
	}
	
	public function toArray($forApi = false, $level = 0)
	{
        return $this->processArray(get_object_vars($this), $level, $forApi);
    }
	
	public function toJSON()
	{
		return json_encode($this->toArray());
	}
    
    
	protected function processArray($array, $level = 0, $forApi=false) 
	{		
		if($level > 2){
			return [];
		}
		
		$result = [];		
        foreach($array as $key=>$v) {
		
		
			if(substr($key, 0,1) == '_'){
				continue;
			}			
			$value = $v instanceof \Closure ? $v : $this->__get($key);			
			
			switch (true){
				
				/* Исключения для Апишки */				
				case $value instanceof Image && $forApi:
					$result[$key] = $value->getBase64();
					break;
				
				/* Стандартные исключения */
				case $value instanceof \DateTime:
					$result[$key] = $value->format('d.m.Y H:i:s');
					break;
				
				case $value instanceof \Closure:
					
					continue;
					break;								
				
				case is_object($value):
						$result[$key] = $value->isExists()? $value->toArray($forApi, $level+1) : [];					
					break;
				
				case is_array($value) && is_object(reset($value)):														
					foreach($value as $i=>$vv){
						if(reset($value) instanceof \stdClass){
							$result[$key][$i] = (array)$vv;
						}else{
							$result[$key][$i] = $vv->toArray($forApi, $level+1);
						}
					}					
					break;
				
				case is_array($value):
					$result[$key] = $value;
					break;
				
				default:
					$result[$key] = (string)$value;
				
			}			
        }
        
        return $result;
    }
	
	public function toApiResponce()
	{
		return $this->toArray(true);
	}
	
	
	public function setCallback(&$property, $callback)
	{
		if($callback instanceof \Closure){
			$property = $callback;
		}
		
		return $this;
	}
}

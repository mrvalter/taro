<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services;

/**
 * @category MED CRM
 */
class FormLoader {
	
	private $errors      = [];
	private $changeProps = [];	
	private $entity;
	
	
	
	public function __construct()
	{
		
	}
	
	public function getEntity()
	{
		return $this->entity;
	}
	
	/**
	 * 
	 * @param object $entity
	 * @param array $data
	 * @return boolean
	 * @throws \FormCheckerException
	 */
	public function loadForm($entity, $data)
	{			
		$dataForSave = [];
		$this->errors = [];
		
		if(!method_exists($entity, '_filters')){
			throw new \FormCheckerException('Не найден метод _filters в объекте '.get_class($entity));
		}		
		
		$filtersData = $entity->_filters();

		if(!is_array($filtersData)){
			throw new \FormCheckerException('Метод _filters объекта '.get_class($entity).' должен возвращать массив');
		}				
			
		foreach($filtersData as $propertyName=>$filtersStr){				
			
			$filters = [];
			if($filtersStr){
				$filters = explode(',', $filtersStr);
			}
			$value = isset($data[$propertyName])? $data[$propertyName] : null;
			
			/* Если данные не переданы и это обновление сущности проходим мимо */
			if(null === $value && $entity->id){
				continue;
			}
            
			/* ID сущности менять запрещено */
			if($propertyName == 'id'){
				continue;
			}
	
			if(isset($filters[0])){
				foreach($filters as $onefilter){
					$className = 'Services\\Filters\\'.ucfirst(strtolower(trim($onefilter)));
					if(!class_exists($className, true)){
						throw new \FormCheckerException('не найден класс '.$className.' который соответствует фильтру '.$onefilter.
						' объекта '. get_class($entity));
					}		
					$o_filter = new $className();

					switch (true){	
						case $o_filter instanceof Interfaces\FilterChecker:
							if(false === $o_filter->execute($value)){
								$this->errors[$propertyName][] = $o_filter->getError();                                
							}
							break;
						case $o_filter instanceof Interfaces\FilterModifier:						
								$value = $o_filter->execute($value);			
							break;
					}

				}
			}
			
			$dataForSave[$propertyName] = $value;		            
		}
        
		if(sizeof($this->errors)){
			return false;
		}
		
		foreach($dataForSave as $propName=>$value){						
			if(null === $entity->id || ($entity->id && $value != $entity->$propName)){				
				$method = 'set'.ucfirst($propName);
				call_user_func_array(array($entity, $method), [$value]);
				$this->changeProps[]= $propName;
			}
		}		
		$this->entity = $entity;
		return true;
	}
	
	public function getErrors()
	{
        $resErrors = array();
        if(sizeof($this->errors)){
            foreach($this->errors as $row=>$errors){
                foreach($errors as $error){
                    $resErrors[] = ['row'=>$row, 'message'=>$error];
                }
            }
        }
		return $resErrors;
	}        
	
	public function getChangeProps()
	{
		return $this->changeProps;
	}
}

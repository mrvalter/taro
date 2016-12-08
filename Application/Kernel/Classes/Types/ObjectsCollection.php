<?php
namespace Kernel\Classes\Types;

/**
 * Коллекция объектов одного класса
 */

class ObjectsCollection extends Collection {					
	
	public function __construct(array $objects=[])
	{
		if(isset($objects[0])){			
			foreach($objects as $i=>$object){
				$this->push($object);
			}
		}
	}
	
	public function getClass()
	{
		return $this->class;
	}		
	
	public function push($object)
	{
		if(!is_object($object)){
			return $this;
		}				
		
		$this->rows[] = $object;
	}
	
	public function getById($id)
	{
		return $this->getOneObjectByProperty('id', $id);
	}
	/**
	 * 
	 * @param string $property
	 * @param mixed $value
	 * @return array
	 */
	public function getObjectsByPropertyValue(string $property, $value): ObjectsCollection
	{
		$return = [];
		
		if(isset($this->rows[0])){
			foreach($this->rows as $row){
				if($row->{'get'.$property()} == $value){
					$return[] = $row;
				}
			}
		}
		
		return new ObjectsCollection($return);
	}
	
	/**
	 * 
	 * @param string $property
	 * @param mixed $value
	 * @return object | null
	 */
	public function getOneObjectByProperty(string $property, $value)
	{
		if(isset($this->rows[0])){
			foreach($this->rows as $row){								
				if($row->{'get'.$property}() == $value){
					return $row;
				}
			}
		}
		
		return null;
	}
}
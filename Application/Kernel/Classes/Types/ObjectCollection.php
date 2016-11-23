<?php
namespace Kernel\Classes\Types;

/**
 * Коллекция объектов одного класса
 */

class ObjectCollection extends Collection{			
	
	private $class = '';
	
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
		
		if(!$this->class){
			$this->class = get_class($object);
		}
		
		if($this->class !== get_class($object)){
			return $this;
		}
		
		$this->rows[] = $object;
	}
	
	/**
	 * 
	 * @param string $property
	 * @param mixed $value
	 * @return array
	 */
	public function getObjectsByPropertyValue(string $property, $value): array
	{
		$return = [];
		
		if(isset($this->rows[0])){
			foreach($this->rows as $row){
				if($row->$property == $value){
					$return[] = $row;
				}
			}
		}
		
		return new ObjectCollection($return);
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
				if($row->$property == $value){
					return $row;
				}
			}
		}
		
		return null;
	}
}
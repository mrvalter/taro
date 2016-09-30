<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Classes;

use Services\View as View;

/**
 * @category MED CRM
 */
class AjaxResponce {
	
	protected $responce = [];
	
	public function __construct(){
		$this->responce['errors'] = [];
		$this->responce['result'] = null;
	}
	
	public function addErrors($errors){
		if(!is_array($errors)){
			$errors = [$errors];
		}
		
		$this->responce['errors'] = array_merge($this->responce['errors'], $errors);
		return $this;
	}
	
	public function setResult($result)
	{
		if($result instanceof View){
			$result = $result->getContentHTML();
		}
		$this->responce['result'] = $result;
		return $this;
	}
	
	public function getResponce(){
						
		return json_encode($this->responce);		
	}
	
	public function isErrors()
	{
		return isset($this->responce['errors'][0]);
	}
}

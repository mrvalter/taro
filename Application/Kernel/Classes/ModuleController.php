<?php
namespace Kernel\Classes;

abstract class ModuleController extends Controller {
   	
	const moduleActionPostfix = 'ActionController';
	
	public function _runController()
	{	
		try{
			
			return parent::_runController();						
			
		} catch (\ControllerMethodNotFoundException $ex) {
			
			$pathParams = $this->_getPathParts();
			$action = $pathParams[0] ?? self::defaultActionName;
			$subAction = ($pathParams[1] ?? self::defaultActionName).self::actionPostfix;
			$class = $this->getBundleName().'\\Controllers\\'.$this->getControllerName().'\\'.ucfirst($action).self::moduleActionPostfix;
			if(!class_exists($class)) {
				throw new \ControllerMethodNotFoundException("Не найден ЭкшнКонтроллер $class контроллера ". get_class($this));
			}
			
			$oController = new $class($this->getServiceContainer(), $this->getUri());						
			
			if(!is_callable([$oController, $subAction])){
				throw new \ControllerMethodNotFoundException("Не найден метод $subAction ЭкшнКонтроллера $class контроллера ". get_class($this));
			}
						
			return self::callAction($oController, $subAction, array_slice($pathParams, 2));
		}
	}
}

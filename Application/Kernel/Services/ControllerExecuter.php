<?php
namespace Kernel\Services;

/**
 * Description of ControllerExecuter
 *
 * @author sworion
 */
class ControllerExecuter {
	
	const controllerPostfix   = 'Controller';
	const actionPostfix       = 'action';
	const moduleActionPostfix = 'ActionController';
	const defaultControllerName = 'Index';
	const defaultActionName = 'index';
	
	private $serviceContainer;
	private $controllerName;
	private $actionName;
	
	private $controllerClass;
	private $Action;
	
	private $controllers = [];
	
	public function __construct(\ServiceContainer $serviceContainer)
	{
		$this->serviceContainer = $serviceContainer;				
	}
	
	public function execute(Uri $uri)
	{
		if(!isset($this->controllers[$controllerName]))
		$refController = $this->controllers[$controllerName] ?? $this->controllers[$controllerName] = new \ReflectionClass($controllerName);
		$refMethod = $refController->getMethod($action);
		$refParams = $refMethod->getParameters();
	}
}

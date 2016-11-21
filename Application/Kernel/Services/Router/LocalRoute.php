<?php
namespace Kernel\Services\Router;


use Kernel\Services\Firewall;
use Kernel\Services\HttpFound\{Response, Uri, Request};
use \Psr\Http\Message\RequestInterface;
/**
 * Description of Route
 *
 * @author sworion
 */
class LocalRoute extends Route{
	const controllerPostfix       = 'Controller';
	const defaultControllerName   = 'Index';     
	const defaultActionName       = 'index';
	const actionPostfix           = 'Action';
	const actionControllerPostfix = 'ActionController';
	
	private $bundle;
	private $controllerName;
	private $config;	
	private $serviceContainer;
	private $request;
	private $uri;
	private $isSystemResponce;
	
	public function __construct(\ServiceContainer $serviceContainer, RequestInterface $request, bool $isSystemResponce = false)
	{
		parent::__construct();
		
		$this->request = $request;
		$this->uri = $request->getUri();		
		$this->serviceContainer = $serviceContainer;
		$this->isSystemResponce = $isSystemResponce;
	}
	
	public function execute(): Response
	{	
		$firewall = $this->getFirewall();
		
		try {

			$controllerClass = $this->findController($this->uri);
			if($this->checkAccess($this->uri)){			
				$params = array_slice($this->uri->getPathParts(), 2);
				return $this->runController($controllerClass, $params);
			}
			
		}catch (\ResponseException $ex){
			
			$code = $ex->getCode();			
			return $this->getSystemResponse(
				$code, 
				$firewall->getPathBySystemCode($code), 
				$ex->getMessage(), 
				$ex->getSysMessage()
			);
			
			
		} 
	}
	
	private function runController( string $controllerClass, array $params=[]): Response
	{
		if(isset($params[0]) && preg_match('~[^a-z09_]+~ui', $params[0])){
			throw new \PageNotFoundException('','Экшен создержит некорректные символы');
		}
		
		$actionName   = $params[0] ?? self::defaultActionName;
		$actionMethod = $actionName.self::actionPostfix;
				
		$refController = new \ReflectionClass($controllerClass);		
		
		if(!$refController->hasMethod($actionMethod)){
			if(!$refController->isSubclassOf('Kernel\Classes\ModuleController')){
				throw new \PageNotFoundException('','Action not found');
			}
			
			$controllerClass = 
				$refController->getNamespaceName().'\\'.
				str_replace(self::controllerPostfix, '', $refController->getShortName()).'\\'.
				ucfirst($actionName).self::actionControllerPostfix;			
			
			if(!class_exists($controllerClass)){
				throw new \PageNotFoundException('','ActionController not found');
			}
			
			$refController = new \ReflectionClass($controllerClass);
			if(isset($params[1]) && !preg_match('~[a-z09_]+~ui', $params[0])){
				throw new \PageNotFoundException('','Экшен ЭкшенКонтроллера создержит некорректные символы');
			}
			
			$actionName   = $params[1]?? self::defaultActionName;
			$actionMethod = $actionName.self::actionPostfix;
			
			if(!$refController->hasMethod($actionMethod)){
				throw new \PageNotFoundException('','ActionController found,  but method not found');
			}
			
			$params = array_slice($params, 2);
			
		}else{
			$params = array_slice($params, 1);			
		}
		
		$refMethod = $refController->getMethod($actionMethod);				
		$callableParams = $this->getCallableParams($refMethod, $params);				
				
		$viewer = $this->serviceContainer->get('viewer');		
		$tpath = $this->getFirewall()->getPathToBundle($this->bundle).'/view/'.$this->controllerName;
		$tnamespace = str_replace('\\', '_', $controllerClass);
		$viewer->addTemplatePath($tpath, $tnamespace);
		var_dump($viewer->render('@'.$tnamespace.'/hello.php'));
				die();
		
		$oController = new $controllerClass($this->serviceContainer, $this->request);
		
		return $refMethod->invokeArgs($oController, $callableParams);
		
	}
	
	private function getCallableParams(\ReflectionMethod $refMethod, array $urlParams=[]): array
	{
		
		$refParams = $refMethod->getParameters();
		
		if(!$refMethod->isVariadic() && count($refParams) < count($urlParams)){
			throw new \PageNotFoundException('', 'Количество переданных параметров в URL превышает количество параметров метода');
		}
		
		$callParams = [];
		foreach($refParams as $i=>$refParam){
			if(!$refParam->isDefaultValueAvailable() && !isset($urlParams[$i])){
				throw new \ResponseException(400, '', 'Не передан обязательный параметр в метод');
			}
			
			switch((string)$refParam->getType()){
				case 'int':
					if(isset($urlParams[$i]) && !is_numeric($urlParams[$i])){
						throw new \ResponseException(404, '', 'Переданный параметр не соответствует типу');
					}
					break;
			}
			
			$callParams[$i] = $urlParams[$i]?? null;
		}
				
		return $callParams;	
		
	}
	
	private function getSystemResponse($code, $path = '', $message='', $systemMessage='')
	{				
		$this->response = $this->response->withStatus($code, $message, $systemMessage);
				
		if($this->isSystemResponce || !$path){
			return $this->response;
		}
		
		$route = new LocalRoute($this->serviceContainer, new Request('GET',$path), true);
		$this->addSubRoute($route);
		$systemResponce = $route->execute();
		
		return $systemResponce->getStatusCode() == 404 ? $this->response : $systemResponce->withStatus($code, $message, $systemMessage);
	}
	
	private function getFirewall(): Firewall
	{
		return $this->serviceContainer->get('firewall');
	}
	
	private function findController(Uri $uri, $throwException = false): string
	{
		
		$pathArr = $uri->getPathParts();	
		$this->bundle = $bundle = isset($pathArr[0])?
			$this->getFirewall()->getBundleByName($pathArr[0]) :
			$this->getFirewall()->getMainPageBundle();

		if(!$bundle){
			throw new \PageNotFoundException('','Бандл не существует!');
		}		

		if(!isset($pathArr[1])){
			$controller = self::defaultControllerName;
		}elseif(preg_match('~[a-z09]+~ui', $pathArr[1])){
			$controller = ucfirst($pathArr[1]);
		}else{
			throw new \ControllerNotFoundException('', 'Непозволительное имя контроллера');
		}						

		$this->controllerName = $controller;
		
		$controllerClass = "$bundle\\Controllers\\$controller".self::controllerPostfix;						

		if(!class_exists($controllerClass)){
			throw new \ControllerNotFoundException('', "Контроллер $controllerClass не существует");
		}
			
		return $controllerClass;
	}
	
	private function checkAccess(Uri $uri): bool
	{
		/* Проверка прав доступа на чтение и запись */
		$firewall = $this->getFirewall();                
		if(!$firewall->checkAccess($uri)){
			if(!$firewall->getSecurity()->isAuthorized()){
				throw new \NonAuthorisedException();
			}else {
				throw new \AccessDeniedException();
			}
		}
		
		return true;
	}
	
	
}

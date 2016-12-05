<?php
namespace Kernel\Services\Router;

use Kernel\Classes\Controller;
use Kernel\Services\Firewall;
use Kernel\Services\HttpFound\{Response, Uri, Request};
use Psr\Http\Message\RequestInterface;
/**
 * Description of Route
 *
 * @author sworion
 */
class LocalRoute extends Route{
	
	
	private $bundle;
	private $controllerName;		
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
		if(isset($params[0]) && preg_match('~[^a-z0-9_.]+~ui', $params[0])){
			throw new \PageNotFoundException('','Экшен создержит некорректные символы');
		}
		
		$actionName   = $params[0] ?? Controller::defaultActionName;
		$actionMethod = $actionName . Controller::actionPostfix;
				
		$refController = new \ReflectionClass($controllerClass);		
		
		if(!$refController->hasMethod($actionMethod)){
			if(!$refController->isSubclassOf('Kernel\Classes\ModuleController')){
				throw new \PageNotFoundException('','Action not found');
			}
			
			$controllerClass = 
				$refController->getNamespaceName().'\\'.
				str_replace(Controller::controllerPostfix, '', $refController->getShortName()).'\\'.
				ucfirst($actionName).Controller::actionControllerPostfix;			
			
			if(!class_exists($controllerClass)){
				throw new \PageNotFoundException('','ActionController not found');
			}
			
			$refController = new \ReflectionClass($controllerClass);
			if(isset($params[1]) && !preg_match('~[a-z09_]+~ui', $params[0])){
				throw new \PageNotFoundException('','Экшен ЭкшенКонтроллера создержит некорректные символы');
			}
			
			$actionName   = $params[1]?? Controller::defaultActionName;
			$actionMethod = $actionName.Controller::actionPostfix;
			
			if(!$refController->hasMethod($actionMethod)){
				throw new \PageNotFoundException('','ActionController found,  but method not found');
			}
			
			$params = array_slice($params, 2);
			
		}else{
			$params = array_slice($params, 1);			
		}
		
		
		$refMethod = $refController->getMethod($actionMethod);				
		$callableParams = $this->getCallableParams($refMethod, $params);
				
		$oController = new $controllerClass($this->serviceContainer, $this->request);
		
		$html = $refMethod->invokeArgs($oController, $callableParams);
		
		return new Response(200, [], $html);
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
				throw new \ResponseException(404, '', 'Не передан обязательный параметр в метод');
			}
			
			switch((string)$refParam->getType()){
				case 'int':
					if(isset($urlParams[$i]) && !is_numeric($urlParams[$i])){
						throw new \ResponseException(404, '', 'Переданный параметр не соответствует типу');
					}
					break;
			}
			
			$callParams[$i] = $urlParams[$i]?? $refParam->getDefaultValue();
		}
				
		return $callParams;	
		
	}
	
	private function getSystemResponse($code, $path = '', $message='', $systemMessage=''): Response
	{	
		
		$this->response = $this->response->withStatus($code, $message, $systemMessage);
		
		if(preg_match('~\.[^\/]*$~',$this->uri->getPath())){
			return $this->response->withStatus(404, 'FILE NOT FOUND');
		}
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
			$controller = Controller::defaultControllerName;
		}elseif(preg_match('~[a-z09]+~ui', $pathArr[1])){
			$controller = ucfirst($pathArr[1]);
		}else{
			throw new \ControllerNotFoundException('', 'Непозволительное имя контроллера');
		}						

		$this->controllerName = $controller;
		
		$controllerClass = "$bundle\\Controllers\\$controller".Controller::controllerPostfix;						

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

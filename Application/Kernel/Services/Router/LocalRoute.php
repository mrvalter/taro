<?php
namespace Kernel\Services\Router;


use Kernel\Services\Firewall;
use Kernel\Services\HttpFound\{Response, Uri};
/**
 * Description of Route
 *
 * @author sworion
 */
class LocalRoute extends Route{
	const controllerPostfix     = 'Controller';
	const defaultControllerName = 'Index';     
	const defaultActionName     = 'index';
	const actionPostfix         = 'Action';
	
	private $bundle;	
	private $config;	
	private $serviceContainer;
	private $uri;
	private $isSystemResponce;
	
	public function __construct(\ServiceContainer $serviceContainer, Uri $uri, bool $isSystemResponce = false)
	{
		parent::__construct();
		
		$this->uri = $uri;
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
		$actionName   = $params[0] ?? self::defaultActionName;
		$actionMethod = $actionName.self::actionPostfix;
		
		$refController = new \ReflectionClass($controllerClass);
		if(!$refController->hasMethod($actionMethod)){
			var_dump('action method not exists');
		}
		var_dump($params);
		return new Response();
		var_dump($controllerClass);
		var_dump($params);
		var_dump('RUN Controller');
		die();
	}
	
	private function getSystemResponse($code, $path = '', $message='', $systemMessage='')
	{				
		$this->response = $this->response->withStatus($code, $message, $systemMessage);
				
		if($this->isSystemResponce || !$path){
			return $this->response;
		}
		
		$route = new LocalRoute($this->serviceContainer, new Uri($path), true);
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
		$bundle = isset($pathArr[0])?
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

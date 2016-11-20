<?php
namespace Kernel\Services;
use ServiceContainer;
use Kernel\Interfaces\{FirewallInterface, ControllerInterface};
use Kernel\Interfaces\RouteInterface;
use Kernel\Services\Firewall;
use Kernel\Services\HttpFound\{ServerRequest, Response, Request, Uri};
use Kernel\Services\Config;
use Kernel\Services\Router\{LocalRoute, CurlRoute, ResponseRoute};

use \Psr\Http\Message\RequestInterface;

/**
 * 
 * @TODO REDIRECTS
 */
class Router {                          
	
	const accessDeniedAction = ['Kernel\System\Http\Controllers\FirewallController', 'accessDenied'];
	const pageNotFoundAction = ['Kernel\System\Http\Controllers\FirewallController', 'pageNotFound'];
	
    const defaultControllerName = 'Index';        
    const controllerPostfix     = 'Controller';
    const bundlePostfix         = '_Bundle';
		
	
    /** @var Firewall */
    private static $firewall = null;
    
    /** @var Config*/
    private static $config = null;
	
	/** @var ServiceContainer */
	private static $serviceContainer = null;
    
    /** @var string */
    private $bundle;
    
    /** @var string */
    private $controller;
    
    /** @var string */
    private $action;                   
	
    /** @var Request */
    private $request;
    
    /** @var Response */
    private $response = null;
    
    /** @var array */
    private $errors;
	
	/** @var array */
	private $route;
    
    /**
     * 
     * @param Request $request
     */
    private function __construct(Request $request)
    {
        
        if(null === $this->getFirewall()){
            throw new \SystemErrorException('Firewall not initialized in Router');
        }
        
		if(null === self::$serviceContainer){
            throw new \SystemErrorException('serviceContainer not initialized in Router');
        }
		
		if(null === self::$config){
            throw new \SystemErrorException('config not initialized in Router');
        }
		
		
        $this->request = $request;
        $this->response = new Response();
        
		$uri = $request->getUri();
		if(!$this->checkUri($uri)){
			return;
		}

		$this->route = $this->createRoute($request->getUri());									
    }                   
	
    /**
     * 
     * @return Firewall
     */
    public function getFirewall(): Firewall
    {
		return self::$firewall;
    }
    
	/**
	 * @TODO CurlRoute
	 * @param Uri $uri
	 * @return RouteInterface
	 */
	private function createRoute(Uri $uri): RouteInterface
	{
		$host = $uri->getHost();
		
        if($host !== 'localhost' && $host !== $_SERVER['HTTP_HOST']){
            return new CurlRoute();
        } 
						
		$firewall = $this->getFirewall();
		
		try {			

			$c = $this->foundController($uri);
			if($this->checkAccess($uri)){			
				$controllerClass = $c;
			}
			
		}catch (\PageNotFoundException $ex) {
			
			$path = $firewall->getNotFoundPath();
			if(!$path){
				return new ResponseRoute($this->response->withStatus(404,'', $ex->getMessage()));
			}
			$this->response = $this->response->withStatus(404);
			$uri = new Uri($path);
			
		}catch (\NonAuthorisedException $ex){
			$path = $firewall->getAuthorisePath();
			
		}catch(\AccessDeniedException $ex){
			
			$path = $firewall->getAccessDeniedPath();
			if(!$path){
				return new ResponseRoute($this->response->withStatus(403,'', $ex->getMessage()));
			}
			
			$this->response = $this->response->withStatus(403);
			$uri = new Uri($path);
		}
		
		
		
		$params = array_slice($this->request->getUri()->getPathParts(), 2);				
		
	}		
	
	private function foundController(Uri $uri, $throwException = false): string
	{
		
		$pathArr = $uri->getPathParts();	
		$bundle = isset($pathArr[0])?
			$this->getFirewall()->getBundleByName($pathArr[0]) :
			$this->getFirewall()->getMainPageBundle();

		if(!$bundle){
			throw new \PageNotFoundException('Бандл не существует!');
		}		

		if(!isset($pathArr[1])){
			$controller = self::defaultControllerName;
		}elseif(preg_match('~[a-z09]+~ui', $pathArr[1])){
			$controller = ucfirst($pathArr[1]);
		}else{
			throw new \ControllerNotFoundException('Непозволительное имя контроллера');
		}						

		$controllerClass = "$bundle\\Controllers\\$controller".self::controllerPostfix;						

		if(!class_exists($controllerClass)){
			throw new \ControllerNotFoundException("Контроллер $controllerClass не существует");
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
	
    /**
     * Получает ответ
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        
        return $this->request;
    }    
    
    /**
     * 
     * @return Config
     */
    public function getConfig(): Config
    {
        
        return self::$config;
    }
    
	/**
	 * 
	 * @param \ReflectionMethod $refMethod
	 * @param RequestInterface $request
	 * @return array 
	 */
	public function getActionParamsValues(\ReflectionMethod $refMethod, RequestInterface $request): array
	{
		$refParams  = $refMethod->getParameters();
		if(!isset($refParams[0])){
			return [];
		}
		
		$pathParams = array_slice($request->getUri()->getPathParts(), 3);
		
		foreach($refParams as $i => $refParam){
			$paramName = $refParam->getName();
			$value = null;
			if(isset($pathParams[$i])){
				$value = $pathParams[$i];
			}elseif(isset($_POST[$paramName])){
				$value = $_POST[$paramName];
			}elseif(isset($_GET[$paramName])){
				$value = $_GET[$paramName];
			}
			
			$paramType = $refParam->getType();
			if(null !== $paramType && null !== $value){
				//$this->getFirewall()->checkValue($value, $paramType);
			}
			
						
		}
		var_dump($refParams);
		//die();
		$actionParams=[];
		
		$getParams = '';
		
		die();
		if(sizeof($refParams)){				
			for($x=0; $x<count($refParams); $x++){
				
				if($refParams[$x]->isVariadic()){
					$actionParams = array_merge($actionParams, $paramsUrl);
					break;
				}
				
				
				if($refParams[$x]->isDefaultValueAvailable()){					
					$actionParams[$x] = $refParams[$x]->getDefaultValue();
				}else{
					$actionParams[$x] = null;
				}		
							
				if(isset($paramsRequest[$refParams[$x]->name])){
					$actionParams[$x] = $paramsRequest[$refParams[$x]->name];					
					unset($paramsRequest[$refParams[$x]->name]);					
				}
				
				if(isset($paramsUrl[$x])){
					$actionParams[$x] = $paramsUrl[$x];
					unset($paramsUrl[$x]);					
				}
				
				if(isset($params[$refParams[$x]->name])){
					$actionParams[$x] = $params[$refParams[$x]->name];
					unset($params[$refParams[$x]->name]);
				}												 
			}			
        }    
				
	}
    /**
     * Выполняет запрос
     * 
     * @TODO CurlRequest
     */
    public function sendRequest(): Response
    {        
        
        $host = $this->request->getUri()->getHost();
        
        if($host === 'localhost' || $host === $_SERVER['HTTP_HOST']){
            return $this->handleRequest();
        }        
    }        
    
	public function getControllerObject(): ControllerInterface
	{
		$controllerClass = "{$this->getBundle()}\\Controllers\\{$this->getController()}Controller";
		
		if(!class_exists($controllerClass)){
			throw new \ControllerNotFoundException("Не найден контроллер $controllerClass");
		}
		
		$oController = new $controllerClass(self::$serviceContainer, $this->getRequest()->getUri());
		
		if(!$oController instanceof ControllerInterface){
			throw new \SystemErrorException("Контроллер {$controllerClass} должен осуществлять интерфейс Kernel\Interfaces\ControllerInterface");
		}
		
		return $oController;
	}
	
    private function handleRequest()
    {
        
		/* Если ответ уже готов, прерываем выполнение */
        if($this->response !== null){
            return $this->response;
        }		        
		
		
		
		
		
		$this->controllerExecuter = new ControllerExecuter(
			self::$serviceContainer,				
			$this->getBundle(),
			$this->getController(), 
			$this->getAction(),
			$this->request->getUri()
		);
		
		
		$viewer = $this->getControllerObject()->_runController();			
		return new Response(200);
    }
    
    /**
     * @return Response
     */
    public function createNeedAuthenticateResponse(): Response
    {
        
        return new Response(401);
    }
    
    /** @return Response */
    public function createAccessDeniedResponse(): Response
    {
        
        return new Response(403);
    }
    
    /**
     * 
     * @param string $error Если стоит ошибка то в не в продакшее выводится ошибка
     * @return Response
     */
    public  function createNotFoundResponse($error): Response
    {
        
        return new Response(404);
    }
    
    /**
     * 
     * @return Response
     */
    public function getResponse(): Response
    {
        
        return $this->response;
    }
	
    /**
     * Возвращает имя бандла как в URL
     * @return string
     */
    public function getBundle(): string
    {
        
        return $this->bundle;
    }                               
	
    /**
     * Возвращает название контроллера без постфикса
     * @return string
     */
    public function getController(): string
    {
        
        return $this->controller;
    }
        
    /**
     * Возвращает название акшена без постфикса
     * @return string
     */
    public function getAction(): string
    {
        
        return $this->action;
    }            					                       

    
    /**
     * 
     * @return \Services\Router
     */
    public function initConfig()
    {	
        
        $bundleDir = $this->getFirewall()->getPathToBundleByname($this->bundle);
        
        $config->addTags([
            '%bundle%'     => $this->bundle,
            '%bundlePath%' => $bundleDir,
            '%public%'     => self::defaultBundleName
        ]);
      
        $config->addDir($bundleDir, false);
        $this->config = $config->makeTags();

        return $this;
    }
    
    public function withFirewall(Firewall $firewall): self
    {
        $this->firewall = $firewall;
        return $this;
    }	
	
    /**
     * 
     * @param type $bundlesPath
     * @return \Services\Router
     * @throws \FileNotFoundException
     */
    public function withBundlesPath($bundlesPath)
    {
            if(!file_exists($bundlesPath)){
                    throw new \FileNotFoundException('Не верно указан путь к Бандлам');
            }
            $this->bundlesPath = $bundlesPath;
            return $this;
    }		        
	
    /**
     * @TODO Сделать 
     * 
     */
    public function redirectToMain() {
       header('Location: '.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST']);
        die();
    }        	   		
	
    /**
     * @TODO Сделать 
     * 
     * @param \Services\RedirectManager $redirectManager
     * @return \Services\Router
     */
    public function createRedirect(RedirectManager $redirectManager)
    {
            $this->redirectManager = $redirectManager;
            $uriPath = $this->request->getRequestUrl();				
            $redirectArr = $this->redirectManager->getRedirectFromRoute($uriPath);

            if(null !== $redirectArr){			
                    $this->bundle         = $redirectArr[0];
                    $this->controller = ucfirst($redirectArr[1]);
                    $this->action     = strtolower($redirectArr[2]);			
            }

            return $this;
    }

    /**
     * @TODO RESPONCE REDIRECT
     * @param Uri $uri
     * @return boolean
     */
    public function checkUri(Uri $uri)
    {
        $path = $uri->getPath();

        if(substr($path, -1) =='/' && $path != "/" && preg_match('~^\/[^/]+\/$~',$path)) {
			header('Location: '.$uri->getScheme().'://'.$uri->getHost().substr($path, 0, -1));
			die();
        }

        return true;
    }	   
	
	
    /**
     * 
     * @param string $uri
     * @param string $method
     * @param array $headers
     * @param string $version
     * @return \Services\Router
     */
    public static function createFromParams($uri, $method='get', $body=null, $headers=[], $version='1.1')
    {
           /* if(!preg_match('~^(https?:\/\/)?([\w\.]+)\.([a-z]{2,6}\.?)(\/[\w\.]*)*\/?$~', $uri, $result))
            {
                    $uri = 'https://'.$_SERVER['HTTP_HOST'].$uri;
            }

            $request = new HttpFound\Request($method, $uri, $headers, $body, $version);
            return new Router($request);*/
    }
	
    /**
     * Создает роутер из серверных переменных запроса
     * @return static
     */    
    static function createFromGlobals()
    {     		
        $request = ServerRequest::fromGlobals();		
        return new Router($request);
    }	
    
    /**
     * 
     * @param Firewall $firewall
     */
    public static function setFirewall(Firewall $firewall)
    {
        if(self::$firewall === null){
			self::$firewall = $firewall;
        }
    }
    
    /**
     * 
     * @param FirewallInterface $firewall
     */
    public static function setConfig(Config $config)
    {
        if(self::$config === null){
                self::$config = $config;
        }
    }
	
	/**
     * 
     * @param FirewallInterface $firewall
     */
    public static function setServiceContainer(ServiceContainer $container)
    {
        if(self::$serviceContainer === null){
			self::$serviceContainer = $container;
        }
    }
}


/**
 * public function getPageMenuUrl()
	{
		if(null !== $this->request){
			$uriPath = $this->request->getRequestUrl();
			
			if(substr($uriPath, 0, 1) == '/'){
				$uriPath = substr($uriPath, 1);
			}
			$urlsArr = [];
			if($uriPath){
				$urlsArr = explode('/', $uriPath );
			}
			$controller = isset($urlsArr[1])? $urlsArr[1] : '';
			$action = isset($urlsArr[2])? $urlsArr[2] : '';
			
			return $this->createPageMenuUrlFromParams($controller, $action);
		}
		
 * 
 * 
 * public function createPageMenuUrlFromParams($controller='', $action='')
	{
		$menuPath  = $controller? '/'.$controller: '/'.$this->defaultController;
		$action = $action && strtolower($action) != $this->defaultAction? '/'.$action: '';
		
		if($action){
			$controller = $controller? '/'.$controller : $this->defaultController;
		}else{
			$controller = $controller && strtolower($controller) != $this->defaultController ? '/'.$controller : '/';
		}
		
		return $controller.$action;
	}		
 */
<?php
namespace Kernel\Services;
use ServiceContainer;
use Kernel\Interfaces\{FirewallInterface, ControllerInterface};
use Kernel\Services\Firewall;
use Kernel\Services\HttpFound\{ServerRequest, Response, Request, Uri};
use Kernel\Services\Config;
use \Psr\Http\Message\RequestInterface;

/**
 * 
 * @TODO REDIRECTS
 */
class Router {                          
	
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
	
    /** @var RequestInterface */
    private $request;
    
    /** @var Response */
    private $response = null;
    
    /** @var array */
    private $errors;
    
    /**
     * 
     * @param Request $request
     */
    private function __construct(RequestInterface $request)
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
        //$this->response = new Response();        
        
		$uri = $request->getUri();
		if(!$this->checkUri($uri)){
			return;
		}

		$pathArr = $uri->getPathParts();
		
		$this->bundle = isset($pathArr[0])?
			$this->getFirewall()->getBundleByName($pathArr[0]) : 
			$this->getFirewall()->getMainPageBundle();

		if(!$this->bundle){
			$this->response = $this->createNotFoundResponse('Can\'t find Bundle');
		}

		$this->controller = isset($pathArr[1])? ucfirst($pathArr[1]) : self::defaultControllerName;
		
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
		
		
		/* Проверка прав доступа на чтение и запись*/
        $firewall = $this->getFirewall();                   
        if(!$firewall->checkAccess($this->request)){
            if(!$firewall->getSecurity()->isAuthorized()){			
                return $this->createNeedAuthenticateResponse();
            }else{
                return $this->createAccessDeniedResponse();
            }      
        }        
						
		$viewer = $this->getControllerObject()->_runController();
		
		
		die();
		        
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
     * @TODO СДЕЛАТЬ РЕДИРЕКТ
     * @param type $controller
     * @param type $action
     * @param type $params
     * @param type $code
     * @throws \RouteException
     */
    public function redirect($controller, $action='', $params=array(), $code=301)
    {				
				
        $bundle = $this->request === null ? $this->getBundle() : $this->getBundleFromUrl();				
        $pathes[0] = $bundle;
        $pathes[1] = strtolower(trim($controller));
        $pathes[2] = strtolower(trim($action));

        if(!$pathes[1]){
                throw new \RouteException('не передан контроллер в Редирект Роутера');
        }

        if(!$pathes[2] || $pathes[2] == $defaultAction){
                unset($pathes[2]);
        }


        if(!isset($pathes[2]) && ($pathes[1]=='' || $pathes[1]==$this->defaultController)){
                $pathes[1] = '';
        }
						
        $url = '/'.implode('/',$pathes);

        if(sizeof($params)){
            foreach($params as $key=>$value){
                    $arrParams[] = "$key=$value";
            }			
            $url .='?'.implode('&', $arrParams);
        }				
		
        if($url){     
            header('Location: '.$url);
            exit();
        }
                
        throw new \RouteException("Не найден путь для редиректа ($routeName)");
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
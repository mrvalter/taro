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
    
    /** @var Firewall */
    private static $firewall = null;
    
    /** @var Config*/
    private static $config = null;
	
	/** @var ServiceContainer */
	private static $serviceContainer = null;
    
    /** @var string */
    private $bundle;        
	
    /** @var RequestInterface */
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
        $this->response = new Response();		
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
	private function createRoute(RequestInterface $request): RouteInterface
	{
		$uri = $request->getUri();
		$host = $uri->getHost();
		
		
        if($host !== 'localhost' && $host !== $_SERVER['HTTP_HOST'] && $host){
            return new CurlRoute();
        } 
								
		return new LocalRoute(self::$serviceContainer, $request);
		
	}		
	
	
	
    /**
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
     * Выполняет запрос
     * 
     * @TODO CurlRequest
     */
    public function sendRequest(): Response
    {   
		
		return $this->routes[] = $this->createRoute($this->request)->execute();				
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
           
    }   	    
	
    /**
     * Создает роутер из серверных переменных запроса
     * @return static
     */    
    static function createFromGlobals(): self
    {     		
        $request = ServerRequest::fromGlobals();		
        return new Router($request);
    }	
		
	/**
     * Создает роутер из переданного значения url
     * @return static
     */    
    static function createFromUrl(string $url=''): self
    {
		
        $request = new Request('GET', $url);
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
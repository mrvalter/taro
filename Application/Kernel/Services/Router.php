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
    	
	/** @var array */
	private static $routes = [];
	
	/** @var ServiceContainer */
	private static $serviceContainer = null;
		
	/*
    public function createNeedAuthenticateResponse(): Response
    {
        
        return new Response(401);
    }
    
    public function createAccessDeniedResponse(): Response
    {
        
        return new Response(403);
    }
    */
	
	/*    
    public  function createNotFoundResponse($error): Response
    {
        
        return new Response(404);
    }
    */	    
	    	
	
	public static function getRoutes(){
		return self::$routes;
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
	
	/**
     * Создает роутер из серверных переменных запроса
     * @return static
     */    
    public static function createFromGlobals(): RouteInterface
    {     		
        $firewall = self::$serviceContainer->get('firewall');
		$request = $firewall->verifyRequest(ServerRequest::fromGlobals());
        return self::$routes[] = self::createRoute($request);
    }
	
	/**
     * Создает роутер из переданного значения url
     * @return static
     */    
    public static function createFromUrl(string $url=''): RouteInterface
    {
		        
        return self::$routes[] = self::createRoute(new Request('GET', $url));
    }
	
	private static function createRoute(RequestInterface $request): RouteInterface
	{

		$uri = $request->getUri();
		$host = $uri->getHost();
				
        if($host !== 'localhost' && $host !== $_SERVER['HTTP_HOST'] && $host){
            return new CurlRoute();
        } 
								
		return new LocalRoute(self::$serviceContainer, $request);
		
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
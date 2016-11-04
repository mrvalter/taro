<?php
namespace Services;

use Services\Interfaces\FirewallInterface;
use Psr\Http\Message\RequestInterface;
use Services\HttpFound\ServerRequest;
use Services\HttpFound\Request;
use Services\HttpFound\Response;
use Services\HttpFound\Uri;
use Services\Config;


/**
 * 
 * @TODO REDIRECTS
 */
class Router {                          
	
    const defaultControllerName = 'Index';
    const defaultActionName     = 'index';    
    const controllerPostfix     = 'Controller';
    const bundlePostfix         = '_Bundle';
    const actionPostfix         = 'Action';
	
    /** @var FirewallInterface*/
    private static $firewall = null;
    
    /** @var Config*/
    private static $config = null;
    
    /** @var string */
    private $bundle;
    
    /** @var string */
    private $controller;
    
    /** @var string */
    private $action;    
    
    /** @var string */
    private $realBundle;            

    /** @var array */
    private $params; 
	
    /** @var Request */
    private $request;
    
    /** @var Response */
    private $response = null;
    
    /** @var array */
    private $errors;
    
    /**
     * 
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        

        if(null === $this->getFirewall()){
            throw new \SystemErrorException('Firewall not initialized');
        }
        
        $this->request = $request;
        //$this->response = new Response();        

        if(null !== $request) {
            $uri = $request->getUri();				               				
            $this->checkUri($uri);
            $pathArr = [];
            $path = substr($uri->getPath(), 1);
            if(trim($path)){
                $pathArr = explode('/', $path);
            }
                                    
            $this->bundle = isset($pathArr[0])?
                $this->getFirewall()->getBundleByName($pathArr[0]) : 
                $this->getFirewall()->getMainPageBundle();
            
            if(!$this->bundle){
                $this->response = $this->createNotFoundResponse('Can\'t find Bundle');
            }
            $this->controller = isset($pathArr[1])? ucfirst($pathArr[1]) : self::defaultControllerName;
            $this->action     = isset($pathArr[1])? strtolower($pathArr[1]) : self::defaultActionName;
        }
        
        $this->params = [];        
        if(isset($pathArr[2])){
            for($i=2;$i<count($pathArr); $i++){
                $this->params[] = $pathArr[$i];
            }
        }
        
    }                   
	
    /**
     * 
     * @return Firewall
     */
    public function getFirewall()
    {
            return self::$firewall;
    }
    
    /**
     * Получает ответ
     * @return \Services\HttpFound\Request
     */
    public function getRequest()
    {
        
        return $this->request;
    }    
    
    /**
     * 
     * @return Config
     */
    public function getConfig()
    {
        
        return self::$config;
    }
    
    /**
     * Выполняет запрос
     * 
     * @TODO CurlRequest
     */
    public function sendRequest()
    {        
        
        $host = $this->request->getUri()->getHost();
        
        if($host === 'localhost' || $host === $_SERVER['HTTP_HOST']){
            return $this->handleRequest();
        }        
    }
    
    private function handleRequest()
    {
                
        /* Если ответ уже готов, прерываем выполнение */
        if($this->response !== null){
            return $this->response;
        }
                        
        $controller = $this->getController();
        $action = $this->getAction();
        
        $firewall = $this->getFirewall();
        
        if(!$firewall->checkAccess($this->request)){
            if(!$firewall->getSecurity()->isAuthorized()){
                return $this->response = $this->createNeedAuthenticateResponse();
            }else{
                return $this->response = $this->createAccessDeniedResponse();
            }
            
        }
        
        $this->response = $this->runAction();
        
    }
    
    
    private function runAction()
    {
        
        $controller = $this->getController();        
        $bundle = $this->getBundle();
        
        $controllerClass = "$bundle\\Controllers\\$controller".'Controller';
        $method = $this->getAction().'Action';
        
        $rights = $this->getFirewall()->getSecurity()->getRights($this->request);
        
        $refController = new \ReflectionClass( $controllerClass );
		        
        if(!$refController->isSubclassOf('\Classes\Controller')){
            throw new \ControllerException("Контроллер должен наследовать класс Classes\Controller ($controllerClass) ");
        }                
        
        $refMethod = $refController->getMethod($method);
        $refParams  = $refMethod->getParameters();
        
        $queryParams = $this->request->getUri()->getQuery();
        $pathParams = $this->params;
        
        var_dump($refParams);
        die('ddssd');
    }
    
    /**
     * @return Response
     */
    public function createNeedAuthenticateResponse()
    {
        
        return new Response(401);
    }
    
    /** @return Response */
    public function createAccessDeniedResponse()
    {
        
        return new Response(403);
    }
    
    /**
     * 
     * @param string $error Если стоит ошибка то в не в продакшее выводится ошибка
     * @return Response
     */
    public  function createNotFoundResponse($error)
    {
        
        return new Response(404);
    }
    
    /**
     * 
     * @return Response
     */
    public function getResponse()
    {
        
        return $this->response;
    }
	
    /**
     * Возвращает имя бандла как в URL
     * @return string
     */
    public function getBundle()
    {
        
        return $this->bundle;
    }                               
	
    /**
     * Возвращает название контроллера без постфикса
     * @return string
     */
    public function getController()
    {
        
        return $this->controller;
    }
        
    /**
     * Возвращает название акшена без постфикса
     * @return string
     */
    public function getAction()
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
    
    public function withFirewall(Firewall $firewall)
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
     * 
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
            if(!preg_match('~^(https?:\/\/)?([\w\.]+)\.([a-z]{2,6}\.?)(\/[\w\.]*)*\/?$~', $uri, $result))
            {
                    $uri = 'https://'.$_SERVER['HTTP_HOST'].$uri;
            }

            $request = new HttpFound\Request($method, $uri, $headers, $body, $version);
            return new Router($request);		
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
     * @param FirewallInterface $firewall
     */
    public static function setFirewall(FirewallInterface $firewall)
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
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
    const defaultBundleName     = 'MatMedV2_Public';
    const controllerPostfix     = 'Controller';
    const bundlePostfix         = '_Bundle';
    const actionPostfix         = 'Action';
	
	static $firewall = null;
    
    /** @var string */
    private $bundle;
    
    /** @var string */
    private $controller;
    
    /** @var string */
    private $action;
    
    /** @var string */
    private $bundlesPath;
    
    /** @var string */
    private $realBundle;            

    /** @var array */
    private $params;

    /** @var Config */
    private $config;    
	
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
        
		if(!$this->getFirewall() instanceof FirewallInterface){
			throw new \SecurityException('Не инициализирован сервис файрволла');
		}
		
		$this->request = $request;        

        if(null !== $request) {
            $uri = $request->getUri();				               				
            $this->checkUri($uri);
			$pathArr = [];
			$path = substr($uri->getPath(), 1);
			if(trim($path)){
				$pathArr = explode('/', $path);
			}
						
            $this->bundle     = isset($pathArr[0])? $pathArr[0] : self::defaultBundleName;
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
        
        return $this->config;
    }
    
    /**
     * Выполняет запрос
     * 
     * @TODO CurlRequest
     */
    public function sendRequest()
    {
		/* Если ответ уже готов, прерываем выполнение */
        if($this->response !== null){
			return $this;
		}
		
        $host = $this->request->getUri()->getHost();
        
        if($host === 'localhost' || $host === $_SERVER['HTTP_HOST']){
            $this->handleRequest();
        }
        
		return $this;
    }
    
    private function handleRequest()
    {
        
        $controller = $this->getController();
        $action = $this->getAction();
        $bundle = $this->getRealBundle();
        $bundlesPath = $this->getFirewall()->getBundlesPath();
        
        $classFile = "{$bundlesPath}/{$bundle}/Controllers/{$controller}Controller";                
        
        $controllerClass = "$bundle\\Controllers\\$controller".'Controller';
		
		var_dump($controllerClass);
		
        $medthod = $action.'Action';
        $refController = new \ReflectionClass( $controllerClass );
        		
        $firewall = $this->getFirewall();
		
        if(!$firewall->getSecurity()->authorize() && !$firewall->checkAccess($this->request)){
            $this->response = $this->createNeedAuthenticateResponse();
            return true;
        }
        
        if(!$this->firewall->checkAccess($request)){
            $this->response = $this->createAccessDeniedResponse();
            return true;
        }
        
        $this->response = $this->runAction();
        
    }
    
    
    private function runAction()
    {
        
        $controller = $this->getController();
        $action = $this->getAction();
        $bundle = $this->getRealBundle();
        $bundlesPath = $this->bundlesPath;
        
        $controllerClass = "$bundle\\Controllers\\$controller".'Controller';
        $medthod = $action.'Action';
        
        $rights = $this->firewall->getSecurity()->getSubRights("$controller/$action");
        
        $refController = new ReflectionClass( $controllerClass );
		        
        if(!$refController->isSubclassOf('\Classes\Controller')){
            throw new ControllerException("Контроллер должен наследовать класс \Classes\Controller ($bundle\\$controller) ");
        }                
        
        $refMethod = $refController->getMethod($action);                
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
    
    public  function createNotFoundResponse()
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
     * Возвращает настоящее имя папки бандла
     * @return string
     */
    public function getRealBundle()
    {
        
        return $this->realBundle;
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
     * @param Config $config
     * @return \Services\Router
     */
    public function withConfig(Config $config)
    {	
		
		$firewall = $this->getFirewall();		
		$bundlesPath = $firewall->getBundlesPath();
		$bundle = $this->getBundle();
				
		$realBundle = $firewall->findBundleByName($bundle.'_bundle');

		if(!$realBundle){
			$this->response = $this->createNotFoundResponse();
			return $this;
		}
		
		$config->addTags([
			'%bundle%'     => $realBundle,
			'%bundlePath%' => "$bundlesPath/$realBundle",
			'%public%'     => self::defaultBundleName
		]);		


		$this->realBundle = $realBundle;
		$config->addFile("$bundlesPath/$realBundle", false);
		$this->config = $config->make();

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
     * 
     * @param string $bundlesPath
     * @param string $bundle
     * @return string|null
     */
    public function findRealBundle($bundlesPath, $bundle)
    {
		$this->getFirewall();
        if(file_exists("$bundlesPath/$bundle")){			
                return $bundle;
        }

        foreach (new \DirectoryIterator($bundlesPath) as $file) {
            if ($file->isDot()) continue;

            if ($file->isDir() && strtolower($file->getFilename()) === $bundle) {
                return $file->getFilename();
            }
        }
        
      //   file not found!
                
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
     * @return \Services\Router
     */
    public function setNotFound()
    {          
        header("HTTP/1.0 404 Not Found");	
		if(strtolower($this->getController()) == 'api'){
			die('nf');
		}
        $this->controller = 'Index';
        $this->action = 'notfound';
		//$this->bundle = self::$_publicBundle;
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
    static function createFromParams($uri, $method='get', $body=null, $headers=[], $version='1.1')
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
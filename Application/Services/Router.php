<?php
namespace Services;

use Psr\Http\Message\RequestInterface;
use Services\HttpFound\ServerRequest;
use Services\HttpFound\Request;
use Services\HttpFound\Responce;
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
	
    
    private $bundle;
    private $controller;
    private $action;
    private $bundlesPath;
	private $realBundle;
	
	/** @var array */
	private $params;
	
	/** @var Config */
	private $config;
	
    /** @var Request */
    private $request;
    
    /** @var Responce */
    private $responce;
        
	/**
	 * 
	 * @param RequestInterface $request
	 */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
        $this->responce = new Responce();
		$this->config = new Config();
		
		if(null !== $request) {
			$uri = $request->getUri();				               				
			$this->checkUri($uri);									
			$pathArr = explode('/', substr($uri->getPath(), 1));		
			$this->bundle     = isset($pathArr[0])? $pathArr[0] : self::defaultBundleName;
			$this->controller = isset($pathArr[1])? ucfirst($pathArr[1]) : self::defaultControllerName;
			$this->action     = isset($pathArr[1])? strtolower($pathArr[1]) : self::defaultActionName;
									
		}
    }                   
	
    /**
     * 
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
	
	public function getResponce()
	{
		
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
		$bundlesPath = $this->bundlesPath;
		$bundle = $this->getBundle();		
		$realBundle = $this->findRealBundle($bundlesPath, $bundle);
		if(null === $realBundle){
			$this->setNotFound();
			die('dddd');
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
		if(file_exists("$bundlesPath/$bundle")){			
			return $bundle;
		}
		
		foreach (new \DirectoryIterator($bundlesPath) as $file) {
			if ($file->isDot()) continue;

			if ($file->isDir() && strtolower($file->getFilename()) === $bundle) {
				return $file->getFilename();
			}
		}
				
		return null;
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
			die();
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
	public function checkUri(Uri $uri){
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
     *  Создает роутер из серверных переменных 
     * @return \Services\Router
     */    
    static function createFromGlobals()
    {     		
        $request = ServerRequest::fromGlobals();		
        return new Router($request);
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
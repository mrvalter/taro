<?php

/** 
 * @autor Fedyakin Alexander
 */

use Composer\Autoload\ClassLoader as ClassLoader;
use Classes\AjaxResponce as AjaxResponce;
use Services\Config as Config;
use Services\Router as Router;
use Services\RedirectManager as RedirectManager;
use Services\View as View;
use Services\Menu as Menu;
use Services\Menu\MenuItem as MenuItem;
use Services\Interfaces\ViewInterface as ViewInterface;
use Services\Logger as Logger;
use Services\Firewall;



include_once 'functions.php';
/**
 * Front Controller (Singleton)
 * Класс включает в себя инициализацию сервисов,
 * инициализация роутера, получение от него ответа
 * 
 * 
 */
class App {
    
    /** @var string Путь до папки бандлов */
    const BUNDLES_PATH = 'src';
    
    /** @var string Путь до папки шаблонов */
    const LAYOUTS_PATH = 'layouts';
    
    /** @var string Путь до папки конфигов */
    const MAIN_CONFIGS_PATH = 'config';

    /** @var string окончания девовского енвиромента, когда показываются ошибки */
    const ENVIROMENT_DEV_POSTFIX = 'dev';
    
    private static $_instance = null;        
    private static $startTime=0;
    private static $widgets=array();
   
    
    /**
     *
     * @var string Название окружения prod | dev 
     */
    private $env;
    
    /** @var string путь к публичной папке приложения */
    private $httpPath;		
    
    /** @var string путь к папке с бандлами */
    private $bundlesPath;		
    
    /** @var string путь к папке с шаблонами */
    private $layoutsPath;
    
    /** @var string путь к папке с шаблонами */
    private $mainConfigPath;
    
    
	
    /** @var ClassLoader */
    private $classLoader;
	        
    /** @var \ServiceContainer Объект контейнера сервисов */
    private $ServiceContainer = null;
    
    
    private function __construct()
    {        
        $this->bundlesPath    = __DIR__.'/'.self::BUNDLES_PATH;
        $this->layoutsPath    = __DIR__.'/'.self::LAYOUTS_PATH;
        $this->mainConfigPath = __DIR__.'/'.self::MAIN_CONFIGS_PATH;                
	
    }
    
    /**
     * 
     * @return \App Возвращает экземпляр Приложения
     */
    public static function o()
    {
        return self::$_instance;
    }        
    
                        
    
    /**
     * Возвращает обсолютный путь до папки конфига
     * @return string 
     */
    public function getMainConfigPath()
    {
        return $this->mainConfigPath;
    }       
    
    public function getPathToSelfBundle()
    {
        
        return $this->bundlesPath.'/'.$this->getService('router')->getBundle();        
    }	    
	
    /**
     * 
     * @return string Возвращает обсолютный пас к папки Application
     */
    public function getAppPath()
    {
        
        return __DIR__;
    }
	
    /**
     * Возвращает значение переменной окружения
     * @return string
     */
    public function getEnv()
    {
        
        return $this->env;
    }
    
    /**
     * Возвращает путь к публичной папке приложения
     * @return string 
     */
    public function getHttpPath()
    {
        
        return $this->httpPath;
    }        	   
	
    public function setClassLoader(ClassLoader $loader)
    {
        
        $this->classLoader = $loader;     
        return $this;
    }
        
    /**
     * Возвращает объект сервиса, сохраняя его в хранилище сервисов
     * 
     * @param string $name Имя сервиса описанное в конфиге
     * @return object|null
     */
    public function getService($name)
    {
        
        if(null !== $this->ServiceContainer){
            return $this->ServiceContainer->get($name);
        }

        return null;
    }  	               
    
    /**
     * Устанавливает значение окружения
     * @param string $env
     * @return \App
     */    
    private function setEnv($env='')
    {        
        $this->env = $env;
        return $this;
    }
    
    /**
     * Устанавливает путь к публичной папке Приложения
     * @param string $httpPath
     */
    private function setHttpPath($httpPath)
    {
        
        $this->httpPath = $httpPath;
        return $this;
    }            
    
    
    
    
    /**
     * Стартует приложение
     * 
     * @param string $httpPath Путь к директории публичной папки приложения
     */
    public static function run($httpPath, ClassLoader $loader)
    {
        if(self::$_instance!=null)
            return false;
		

        self::$startTime = microtime(true);
        
        if(self::$_instance !== null)
            return false;                
                
        self::$_instance = $App = new App();	
        
        $App->setEnv(getenv('APP_ENV'))
            //->setErrorHandler()
            ->setHttpPath($httpPath)                  
            ->setClassLoader($loader)            
            ->initApplication();            
		
        try {
            $responce = $App->runHttpApplication();
        }catch (\Exception $e){
            $responce =$App->getService('firewall')->getExceptionResponse($e);
        }
		
        var_dump($responce);
        exit();
    }
    
	/** 
	 * Запускает приложение в редиме консоли
	 */
    public function runC()
    {
        $App = self::$_instance = new App(); 
        $App->setEnv('console')
            //->setErrorHandler()                            
            ->setClassLoader($loader)
            ->initApplication();            
    }
    
    
    /**
     * @todo кешировать конфигурацию
     * Инициализация сервисов
     * @return string HTML 
     */
    private function initApplication()
    {
                        
        ob_start();
                
        try {                                   
        
            /* Инициализируем Контейнер сервисов */
            $this->ServiceContainer = new ServiceContainer();
            
            /* Инициализируем сервис конфига */                 
            $config = new Config($this->getEnv());
            $config->addDir($this->mainConfigPath, ['config'])
                    ->addFile($this->mainConfigPath.'/firewall.php', 'firewall', true);
            
            /* Подгружаем сервисы */
            $this->ServiceContainer = new ServiceContainer($config->getValue('services'));
            $this->ServiceContainer->addService('config', $config);
            
            $sessionStorage = $this->ServiceContainer->get('session_storage')->start();            

            /* Инициализируем файрволл */
            $firewall = new Firewall($this->ServiceContainer->get('security'), $config->get('firewall'), $this->classLoader, $this->bundlesPath);
            $this->ServiceContainer->addService('firewall', $firewall); 
            
            Router::setFirewall($firewall);
            Router::setConfig($config);

        }catch(\Exception $e){            
                echo 'Системная ошибка !<br />';			
                echo $e->getMessage().'<br />';
                $e->getTraceAsString().'<br />';
                die();
        }		
		
        return $this;		       
    }
    
    /**
     * Запуск контроллера
     */
    private function runHttpApplication()
    {                
        $responce = Router::createFromGlobals()               
        ->sendRequest();
        
        var_dump($responce);

    }
        
    private function buildTmpRequestVars()
    {
        $request = self::getTmp('request');
        if($request){
            foreach($request as $key=>$value){
                if(!isset($_REQUEST[$key])){
                    $_REQUEST[$key] = $value;
                }
            }
        }
        
    }
    
    public function getDefaultView()
    {
        $Viewconfig = $this->getService('_config')->get('project_descr');		
		$View = new View($Viewconfig);		
        $View->setLayoutPath($this->appPath.'/'.'layouts');
        return $View;
        
    }    
	
    public function mooveSessionUnreadToTmp()
    {
            if(isset($_SESSION['_SYSTEM']['unread'])){
                    $_SESSION['_SYSTEM']['tmp'] = $_SESSION['_SYSTEM']['unread'];
                    unset($_SESSION['_SYSTEM']['unread']);
            }
    }
    
    public function unsetSessionTmp()
    {		
        if(isset($_SESSION['_SYSTEM']['tmp'])){
            unset($_SESSION['_SYSTEM']['tmp']);
        }
    }
    
    
    /* Общие Методы */

	/**
     * Возвращает сообщение об ошибке
     * @return string 
     */
    public static function getDanger()
    {
        if(isset($_SESSION['_SYSTEM']['tmp']['danger'])){
            return htmlspecialchars($_SESSION['_SYSTEM']['tmp']['danger']);
        }
    }  
    
    /**
     * Возвращает сохраненное сообщение 
     * @return string 
     */
    public static function getMessage()
    {
        if(isset($_SESSION['_SYSTEM']['tmp']['message'])){
            return htmlspecialchars($_SESSION['_SYSTEM']['tmp']['message']);
        }				
    }  
    
    /**
     * Возвращает сообщение WARNING
     * @return string 
     */
    public static function getWarning()
    {
        if(isset($_SESSION['_SYSTEM']['tmp']['warning'])){
            return htmlspecialchars($_SESSION['_SYSTEM']['tmp']['warning']);
        }
    }  
    
    /**
     * Устанавливает сообщение об ошибке которое передается на следующую страницу, после чего удаляется из памяти
     * @param string $alert 
     */
    public static function setDanger($alert)
    {
        $_SESSION['_SYSTEM']['unread']['danger'] = htmlspecialchars($alert);
    }
    
    /**
     * Устанавливает сообщение которое передается на следующую страницу, после чего удаляется из памяти
     * @param string $message 
     */
    public static function setMessage($message)
    {
        $_SESSION['_SYSTEM']['unread']['message'] = htmlspecialchars($message);
    }
    
     /**
     * Устанавливает сообщение WARNING которое передается на следующую страницу, после чего удаляется из памяти
     * @param string $warning 
     */
    public function setWarning($warning)
    {
        $_SESSION['_SYSTEM']['unread']['warning'] = htmlspecialchars($warning);
    }
    
    public static function addTmp($key, $value)
    {
        $_SESSION['_SYSTEM']['tmp']['DATA'][$key] = $value;
    }
    
    public static function getTmp($key)
    {
        return isset($_SESSION['_SYSTEM']['tmp']['DATA'][$key]) ? $_SESSION['_SYSTEM']['tmp']['DATA'][$key] : '';
    }
    
    public static function getNotFoundView(){
            $app = self::o();
            $Router = $app->getService('router');
            $Router->setNotFound();		
            return $app->runController($Router);

    }
	
    public static function insert($controller, $action, $params=[], MenuItem $right=null)
    {
        $app = self::o();      
        $Router = $app->getService('router');
        $newRouter = new Services\Router();
        $newRouter->createFromParams($Router->getBundle(), $controller, $action);
        return $app->runController($newRouter, $params, $right);
    }		
	
	public static function widget($widget, $params=array(), $isPublic=false)
	{		
		$app = App::o();
		$Router = $app->getService('router');			
		$bundle = $isPublic? self::PUBLIC_BUNDLE : $Router->getBundleFromUrl() ;
		ksort($params);
		$hash = md5($bundle.$widget.serialize($params));
		if(isset(self::$widgets[$hash])){
			return self::$widgets[$hash];
		}
				
		$newRouter = new Router();        
		$newRouter->createFromParams($bundle, 'Widgets', $widget);        
		return $app->runController($newRouter, $params)->getContentHTML();
	}
	
	public static function makeUrl($controller='', $action='', $params=[], $bundle='')
	{
		$app = self::o();
		$router = $app->getService('router');
		return $router->makeUrlFromParams($controller, $action, $params, $bundle);
	}
	
	public static function getSelfLink()
	{
            $app = self::o();
            $router = $app->getService('router');
            return self::makeUrl(strtolower($router->getController()), strtolower($router->getAction()));
	}
	
	public function printConsole($str)
	{
            if(!$str){
                    return;
            }
            $json = addslashes(json_encode($str));
            echo "<script>console.log(JSON.parse('".$json."'))</script>";
	}
}
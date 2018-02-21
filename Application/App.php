<?php
include_once 'functions.php';

use Composer\Autoload\ClassLoader as ClassLoader;
use Kernel\Services\{FileDataStorage, Console};
use Kernel\Services\Config\Config;
use Kernel\Services\Router\Router;
use Kernel\Services\Firewall\Firewall;
use Kernel\Services\ServiceContainer\ServiceContainerCreator;
use Kernel\Classes\Repository;


/**
 * Front Controller (Singleton)
 * Класс включает в себя инициализацию сервисов;
 * инициализацию роутера, отправка ему запроса, получение от него ответа.
 * 
 */
class App {
    
    /** @var string Путь до папки бандлов */
    const BUNDLES_PATH = 'src';
    
    /** @var string Путь до папки шаблонов */
    const LAYOUTS_PATH = 'layouts';
    
    /** @var string Путь до папки конфигов */
    const MAIN_CONFIGS_PATH = 'config';

    /** @var string окончания девовского енвиромента, когда показываются ошибки, собирается статистика */
    const ENVIROMENT_DEV_POSTFIX = 'dev';
	
	/**
	 * @var string какой тип конфига использовать (php, yml)
	 */
	const CONFIG_EXTENSION = 'PHP';
	
	const CACHE_PATH = __DIR__.'/cache/kernel';
	
	const CACHE_ENABLED = true;
	
	const CONFIG_CACHE_ENABLED = true;
	
    
    private static $_instance = null;    
    private static $widgets=array();
   
    
    /** @var string Название окружения prod | dev */
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
	        
    /** @var ServiceContainerInterface Объект контейнера сервисов */
    private $ServiceContainer = null;
	
	/** @var Config */
	private $config = null;
    
    
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
    public static function o(): App
    {
        return self::$_instance;
    }        
       
    /**
     * 
     * @return string Возвращает обсолютный пас к папки Application
     */
    public function getAppPath(): string
    {
        
        return __DIR__;
    }
	
    /**
     * Возвращает значение переменной окружения
     * @return string
     */
    public function getEnv(): string
    {
        
        return $this->env;
    }
    
    /**
     * Возвращает путь к публичной папке приложения
     * @return string 
     */
    public function getHttpPath(): string
    {
        
        return $this->httpPath;
    }        	   
	
    public function setClassLoader(ClassLoader $loader): self
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
    private function setEnv($env=''): self
    {        
        $this->env = $env;
        return $this;
    }
    
    /**
     * Устанавливает путь к публичной папке Приложения
     * @param string $httpPath
     */
    private function setHttpPath($httpPath): self
    {
        
        $this->httpPath = realpath($httpPath);		
        return $this;
    }            
                
    /**
     * Стартует приложение
     * 
     * @param string $httpPath Путь к директории публичной папки приложения
	 * @param ClassLoader $loader
     */
    public static function run($httpPath, ClassLoader $loader)
    {
        if(self::$_instance!=null)
            return false;
		

        $startTime = microtime(true);
        
        if(self::$_instance !== null)
            return false;                
                
        self::$_instance = $App = new App();	
        		
        $App->setEnv(getenv('APP_ENV'))            
            ->setHttpPath($httpPath)        
            ->setClassLoader($loader)			
			->initConfig()
            ->initApplication();            
		
        try {
            $response = Router::createFromGlobals()->execute();			
        }catch (\Exception $e){
            $response =$App->getService('firewall')->getExceptionResponse($e);
        }
	
		if($response->getStatusCode() !== 200){
			var_dump($response);
		}else{
			echo $response;	
		}
		
        $time = microtime(true) - $startTime;
        echo '<script>console.log("'.sprintf('Скрипт выполнялся %.4F сек.', $time).'");</script>';        
        
        exit();
    }
    
	/** 
	 * Запускает приложение в режиме консоли
	 */
    public function runConsole($httpPath, ClassLoader $loader)
    {
        $App = self::$_instance = new App(); 
        $App->setEnv('console')
            ->setHttpPath($httpPath)
            ->setClassLoader($loader)
			->initConfig()					
            ->initApplication()
			->runConsoleApplication();
		
		echo 'ConsoleRun';
    }		
    
	private function runConsoleApplication()
	{		
		$console = new Console($this->ServiceContainer, $this->httpPath);
		$console->run();		
	}		
	
	/**
	 * Инициализирует Конфиг
	 * @return \self
	 */
	private function initConfig(): self
	{
		$useCache = strtolower(self::CONFIG_EXTENSION) != 'php' 
				&& self::CACHE_ENABLED 
				&& self::CONFIG_CACHE_ENABLED 
				&& substr($this->env, -3)!='dev';
		
		$cachePath = self::CACHE_PATH.'/config/conf_'.self::CONFIG_EXTENSION.'.cch';
		
		if($useCache && file_exists($cachePath)){			
			$config = Config::makeConfigFromArray(unserialize(FileDataStorage::read($cachePath)), self::CONFIG_EXTENSION, $this->env);
			return $config;
		}
		
		$config = Config::makeConfigFromDir(
				self::CONFIG_EXTENSION, 
				$this->getEnv(),
				$this->mainConfigPath,				
				['%App%' => $this->getAppPath()]);					
		
		if($useCache && !file_exists($cachePath)){
			$dataStr = serialize($config->getValue());
			FileDataStorage::touch($cachePath, $dataStr);
		}
		
		$this->config = $config;
		return $this;
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
			$config = $this->config;
			
            /* Подгружаем сервисы */
            $this->ServiceContainer = new ServiceContainer($config->get('services'));
			$this->ServiceContainer->addService('config', $config, true);
			
            Repository::setServiceContainer($this->ServiceContainer);
            Router::setServiceContainer($this->ServiceContainer);
			
			/* Стартуем сессию */
            $this->ServiceContainer->session_storage->start();
			
            /* Инициализируем файрволл */
            $firewall = new Firewall($this->ServiceContainer->security, $config->get('firewall'), $this->classLoader, $this->bundlesPath);
            $this->ServiceContainer->addService('firewall', $firewall, true);
                        
            

        }catch(\Exception $e){            
                echo 'Системная ошибка !<br />';			
                echo $e->getMessage().'<br />';
                $e->getTraceAsString().'<br />';
				var_dump($e->getTrace());
                die();
        }/*catch(\Throwable $e){
			var_dump($e);
			die();
		}	*/
		
        return $this;		       
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
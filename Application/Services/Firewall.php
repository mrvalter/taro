<?php
namespace Services;
use Services\Interfaces\FirewallInterface;
use Services\Security\Interfaces\SessionStorageInterface;
use Services\Security\Interfaces\UserRepositoryInterface;
use Services\Security\Interfaces\AuthenticatorInterface;
use Services\Security\Authentication\AuthenticationManager;
use Services\Security\Csrf\CsrfManager;
use Services\Security\Security;
use Services\HttpFound\Response;
use Services\Config;
use Services\Router;

use Composer\Autoload\ClassLoader;

use Psr\Http\Message\RequestInterface;


/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */

/**
 * @category MED CRM
 */

/**
 * Класс по сути проверяет входящие данные, 
 * модифицирует исходящие данные если это требуется.
 * Пункт в конфиге - security
 */
class Firewall implements FirewallInterface{
    
    
    /** @var array */
    private $config;    
	
    /** @var Security */
    private $security;    
    	
    /** @var boolean */
    private $xdebugLoaded;
	
	/** @var ClassLoader */
	private $autoloader;
	
	/** @var array */
	private $requireBundles;
	
	/** @var string */
	private $bundlesPath;
    
	/** @var integer */
	private $errorReporting;
	
	/**
     * 
     * @param Config $config
     */
    public function __construct(
		SessionStorageInterface $sessionStorage, 
		AuthenticatorInterface $authentificator,
		UserRepositoryInterface $userRepository,
		ClassLoader $autoloader
		
	){
        $sessionStorage->start();
		
		$authenticationManager = new AuthenticationManager (
            $authentificator,
            $sessionStorage
        );
		
		$this->autoloader = $autoloader;
        $this->security = new Security($authenticationManager, new CsrfManager(), $userRepository, $sessionStorage);        
        $this->xdebugLoaded = extension_loaded('xdebug');
		$this->requireBundles = [];
		
    }      
    
	/**
     * 
     * @return  Security
     */
    public function getSecurity()
    {
        return $this->security;
    }
	
	/**
	 * 
	 * @return string
	 */
	public function getBundlesPath()
	{
		return $this->bundlesPath;
	}
	
	public function setConfig(array $config=[])
	{
		
		$this->config = $config;
		$this->bundlesPath = $config['bundles_path'];
		$this->requireBundles();
		$this->setErrorReporting();
		
		return $this;
	}    		
		
	public function getExceptionResponse(\Exception $exception)
    {                
        
        if(in_array($this->errorReporting, [E_ALL, E_ERROR] )){
			
		}
		
		
		die('eee');
        if($this->xdebugLoaded){
            //xdebug_print_function_stack( $exception->getMessage() );
        }else{
            $response = new Response(404, [], $exception->getMessage());            
        }
        
        return $response;        
    }
	
	public function setErrorReporting()
	{		
		$errorH = isset($this->config['error_reporting'])? $this->config['error_reporting'] : 0;
		if($errorH){
			ini_set('display_errors', TRUE);
		}else{
			if($this->xdebugLoaded){
				xdebug_disable();
			}
		}
		
		error_reporting($errorH);
		
		$this->errorReporting = $errorH;
		return $this;
	}
	
	/**
	 * Ищет подключенный бандл по имени
	 * @param string $name
	 * @return string|false
	 */
	public function findBundleByName($name)
	{
		$name = strtolower($name);
		return isset($this->requireBundles[$name])? $this->requireBundles[$name] : false;
	}    
    	
    /**
     * 
     * @param Request $request
     * @return boolean
     */
    public function checkAccess(RequestInterface $request)
    {                                
        $publicPathes = isset($this->config['public_urls'][0])? $this->config['public_urls'][0] : [];
		$path = $request->getUri()->getPath();
		
        return false;
    }
    
    /**
     * 
     * @param Request $request
     * @return boolean
     */
    public function verifyRequest(RequestInterface $request)
    {
        
        return false;
    }               
    
	/**
	 * Добавляет разрешенные бандлы в автозагрузку
	 * @param string $bundlesPath
	 * @return \Services\Firewall
	 */
    private function requireBundles()
	{
		
		if(!isset($this->config['require_bundles'][0]) || !$this->bundlesPath){
			return $this;
		}
		
		$this->requireBundles = [];
		foreach($this->config['require_bundles'] as $bundleName) {
			$this->autoloader->add("$bundleName\\", $this->bundlesPath);
			$this->requireBundles[strtolower($bundleName)] = $bundleName;
		}
		
		return $this;
	}
    
}

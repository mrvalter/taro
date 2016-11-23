<?php
namespace Kernel\Services;

use Kernel\Interfaces\FirewallInterface;
use Kernel\Services\Security\Security;
use Kernel\Services\HttpFound\{Uri, Response};
use Kernel\Services\Config;
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
    private $requireBundles;
    
    /** @var array*/
    private $publicUrls;
    
    /** @var string */
    private $mainPageBundle;
    
    /** @var string*/
    private $bundlesPath;
    
    /** @var Security */
    private $security;    
    
    /** @var boolean */
    private $xdebugLoaded;
	
	/** @var array */
	private $systemResponses;
    
    /**
     * @param Security $security
     * @param array $config
     */
    public function __construct(Security $security, Config $config, $classLoader, $bundlesPath)
    {
        if(!file_exists($bundlesPath)){
            throw new \SystemErrorException('Path to Bundle\'s dir not found', 'bundlesPath: '.$bundlesPath);
        }
        
		$this->requireBundles = $this->getRequireBundlesFromConfig($config, $classLoader, $bundlesPath);
		$this->publicUrls = $this->getPublicUrlsFromConfig($config);						
        $mainPageBundle   = $config->getValue('main_page_bundle');
        $this->mainPageBundle = $mainPageBundle && isset($this->requireBundles[strtolower($mainPageBundle)])?
            $this->requireBundles[strtolower($mainPageBundle)] :
            null;
        
        $this->security = $security;
        $this->bundlesPath = $bundlesPath;
        $this->xdebugLoaded = extension_loaded('xdebug');
		$this->systemResponses = $config->getValue('system_responses');
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

    /**
     * 
     * @return string|null
     */
    public function getMainPageBundle()
    {
        return $this->mainPageBundle;
    }
    
	
	public function getPathBySystemCode($code): string
	{
		return $this->systemResponses[$code] ?? '';
	}		
	
	/**
     * Ищет подключенный бандл по имени
     * @param string $name
     * @return string|false
     */
    public function getBundleByName($name)
    {
        $name = strtolower($name);
        return isset($this->requireBundles[$name])? $this->requireBundles[$name] : null;
    }    
    
    public function getPathToBundle($name)
    {
        if(!in_array($name, $this->requireBundles)){
			return null;
		}
        
        return $this->bundlesPath.'/'.$name;
    }            
	
	public function getExceptionResponse(\Exception $exception)
    {                        
        var_dump($exception);
        return new Response(503);
        
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
	
    public function setConfig(array $config=[])
    {

        $this->config = $config;
        $this->bundlesPath = $config['bundles_path'];
        $this->requireBundles();
        $this->setErrorReporting();

        return $this;
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
     * @TODO check Access by rights POST - right W, get - right R
     * @param Request $request
     * @return boolean
     */
    public function checkAccess(Uri $uri)
    {           		
		return true;
		/* Возможно путь является публичным */		
		$path = $request->getUri()->getPath();
		if(isset($this->publicUrls[0])){
			foreach($this->publicUrls as $pUrl){	
				if(preg_match($pUrl, $path)){
					return true;
				}
			}
		}			

        if(!$this->getSecurity()->authorize()){
            return false;
        }

        
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
	 * 
	 * @param Config $config
	 * @param object $classLoader
	 * @return array
	 * @throws \FirewallException
	 */
	private function getRequireBundlesFromConfig(Config $config, $classLoader, $bundlesPath)
	{		
		$returnRequireBundles = [];		
		$requiredbundles = $config->getValue('required_bundles');
        if(null !== $requiredbundles && sizeof($requiredbundles)){
            foreach($requiredbundles as $name=>$bundle){
                $bundle = trim($bundle);
                $name = trim($name);
                if(!$bundle || !$name){
                    throw new \FirewallException('Requires Bundles has emprty rows');
                }
                $returnRequireBundles[strtolower($name)] = $bundle;
                $classLoader->add($bundle, $bundlesPath);
            }
        }
		
		return $returnRequireBundles;
	}
	
	/**
	 * 
	 * @param Config $config
	 * @return array
	 */
	private function getPublicUrlsFromConfig(Config $config)
	{
		$returnPublicUrls = [];
		$publicUrls = $config->getValue('firewall', 'public_urls');
		if(null !== $publicUrls && isset($publicUrls[0])){
			foreach($publicUrls as $pUrl){
				if(substr($pUrl, 0, 1) == '~' && strpos($pUrl, '~', 1) !== false ){
					$pattern = $pUrl;
				}else{
					$pattern = '~^'.str_replace('/','\\/', preg_replace('~[^a-z/-0-9]~ui','', $pUrl)).'(?=\\/|$)~ui';
				}
				$returnPublicUrls [] = $pattern;
			}				
		}
		
		return $returnPublicUrls;
	}
    
}

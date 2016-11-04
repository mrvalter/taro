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
    
    /**
     * @param Security $security
     * @param array $config
     */
    public function __construct(Security $security, Config $config, $classLoader, $bundlesPath)
    {
        if(!file_exists($bundlesPath)){
            throw new \SystemErrorException('Path to Bundle\'s dir not found', 'bundlesPath: '.$bundlesPath);
        }
        
        $requiredbundles = $config->getValue('firewall', 'required_bundles');
        
        if(null !== $requiredbundles && sizeof($requiredbundles)){
            foreach($requiredbundles as $name=>$bundle){
                $bundle = trim($bundle);
                $name = trim($name);
                if(!$bundle || !$name){
                    throw new \ConfigException('Requires Bundles has emprty rows');
                }
                $this->requireBundles[strtolower($name)] = $bundle;
                $classLoader->add($bundle, $bundlesPath);
            }
        }
                
        $this->publicUrls = $config->getValue('firewall', 'public_urls');
                
        $mainPageBundle       = $config->getValue('firewall', 'main_page_bundle');

        $this->mainPageBundle = $mainPageBundle && isset($this->requireBundles[strtolower($mainPageBundle)])?
            $this->requireBundles[strtolower($mainPageBundle)] :
            null;
        
        $this->security = $security;
        $this->bundlesPath = $bundlesPath;
        $this->xdebugLoaded = extension_loaded('xdebug');     
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
        
        var_dump($exception);
        die();
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
    public function getBundleByName($name)
    {
        $name = strtolower($name);
        return isset($this->requireBundles[$name])? $this->requireBundles[$name] : null;
    }    
    
    public function getPathToBundleByName($name)
    {
        if(null === $this->findBundleByName($name)){
            return null;
        }
        
        return $this->bundlesPath.'/'.$this->getBundleByName($name);
    }            
    
    /**
     * @TODO check Access by rights
     * @param Request $request
     * @return boolean
     */
    public function checkAccess(RequestInterface $request)
    {   
        $path = $request->getUri()->getPath();
                 
        /* Проверяем на публичный доступ */
        if(isset($this->publicUrls[0])){                   
            foreach($this->publicUrls as $pattern){
                if(preg_match($pattern, $path)){
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
    
    public function buildExceptionResponse(\Exception $exception, Router $router)
    {                
        
        //var_dump($exception->);
        if($this->xdebugLoaded){
            //xdebug_print_function_stack( $exception->getMessage() );
        }else{
            $responce = new Response(404, [], $exception->getMessage());            
        }
        
        return $responce;        
    }           
    
}

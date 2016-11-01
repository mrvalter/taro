<?php
namespace Services;

use Services\Config;
use Services\Security\Security;
use Psr\Http\Message\RequestInterface;
use Services\Security\Authentication\AuthenticationManager;
use Services\Security\Csrf\CsrfManager;
use Services\Security\Interfaces\SessionStorageInterface;
use Services\Router;
use Services\HttpFound\Response;



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
class Firewall {
    
    
    /** @var array */
    private $config;
    
    /** @var array */
    private $requireBundles;
    
    /** @var Security */
    private $security;    
    
    /** @var boolean */
    private $xdebugLoaded;
    
    /**
     * @param Security $security
     * @param array $config
     */
    public function __construct(Security $security, $config=[], $classLoader, $bundlesPath)
    {
        if(isset($config['required_bundles']) && sizeof($config['required_bundles'])){
            foreach($config['required_bundles'] as $name=>$bundle){
                $this->requireBundles[strtolower($name)] = $bundle;
                $classLoader->add('Swar_Bundle\\', $bundlesPath);
            }
        }
                
        $this->config = $config;        
        $this->security = $security;
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
     * @param string $bundle
     * @return string|false
     * 
     */
    public function getRealBundleByName($bundle)
    {        
        $bundle = strtolower($bundle);
        if(isset($this->requireBundles[$bundle])){
            return $this->requireBundles[$bundle];
        }
        
        return false;
    }
    
    /**
     * 
     * @param Request $request
     * @return boolean
     */
    public function checkAccess(RequestInterface $request)
    {                                
        
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

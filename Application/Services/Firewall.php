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
    
    /** @var Security */
    private $security;
    
    /** @var SessionStorageInterface */
    private $sessionStorage;
    
    /** @var boolean */
    private $xdebugLoaded;
    /**
     * 
     * @param Config $config
     */
    public function __construct(SessionStorageInterface $sessionStorage, $config=[], $enviroment='prod')
    {
        
        $this->sessionStorage = $sessionStorage->start();      
        $this->config = $config;        
        $this->security = $this->createSecurity();
        $this->xdebugLoaded = extension_loaded('xdebug');
        $this->enviroment = $eviroment;
                
        
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
    
    public function createSecurity()
    {                
        $authenticationManager = new AuthenticationManager(
            $this->serviceContainer->get('authenticator'),
            $this->sessionStorage
        );
        
        $csrfManager = new CsrfManager();
        $userRepository = $this->serviceContainer->get('user_repository');        
        $security = new Security($authenticationManager, $csrfManager, $userRepository, $this->sessionStorage);
        
        return $security;
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

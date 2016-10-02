<?php
namespace Services\Security\Authorization;
use Services\Security\Interfaces\AutorizationManagerInterface;

/**
 *
 * @author FedyakinAS
 */
abstract class AuthorizationManagerAbstract implements AutorizationManagerInterface {    
    
    /** @var SessionHandlerInterface */
    private $sessionStorage;    
    
    public function __construct(SessionHandlerInterface $sessionStorage)
    {
        $this->sessionStorage = $sessionStorage;
    }
    
    
    
    public function authorize() {
        
    }
    
    abstract public function authentificate($login, $password);
    
}

<?php

namespace Services\Security;
use Services\Security\Authentication\AuthenticationManager;
use Services\Security\Interfaces\AuthenticatorInterface;
use Services\Security\Csrf\CsrfManager;

use Services\Security\Interfaces\SessionStorageInterface;
use Services\Security\Interfaces\UserRepositoryInterface;
use Psr\Http\Message\RequestInterface;


/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */

/**
 * @category MED CRM
 */
class Security {
        
    private $authenticationManager;
    private $csrfManager;
    private $sessionStorage;
    private $userRepository;
    private $user;    
    
    public function __construct(        
        AuthenticatorInterface $authenticator,              
        UserRepositoryInterface $userRepository,
        SessionStorageInterface $sessionStorage
    ) {
                
        $this->authenticationManager = new AuthenticationManager ($authenticator, $sessionStorage);
        $this->csrfManager = new CsrfManager();
        $this->userRepository = $userRepository;
        $this->sessionStorage = $sessionStorage->start();
        $this->user = null;
    }
    
    public function authorize()
    {
                
        if(!$this->sessionStorage->isAuthorized()){
            return false;
        }
        
        $user = $this->userRepository->getUserByLogin(
            $this->sessionStorage->getLogin(), 
            $this->sessionStorage->getDomen()
        );
        
        if(!$user->isExists()){
            return false;
        }
        
        $this->user = $user;
        
        return true;
    }
    
    public function isAuthorized()
    {
        return $this->user !== null;
    }   
    
    
    public function getRights(RequestInterface $request)
    {
        return [];
    }
}

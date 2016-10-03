<?php

namespace Services;
use Services\Security\Authentication\AuthenticationManager;
use Services\Security\Csrf\CsrfManager;

use Services\Security\Interfaces\SessionStorageInterface;
use Services\Security\Interfaces\UserRepositoryInterface;


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
        AuthenticationManager $authenticationManager, 
        CsrfManager $csrfManager,      
        UserRepositoryInterface $userRepository,
        SessionStorageInterface $sessionStorage
    ) {
                
        $this->authenticationManager = $authenticationManager;
        $this->csrfManager = $csrfManager;
        $this->userRepository = $userRepository;
        $this->sessionStorage = $sessionStorage;
        $this->user = null;
    }
    
    public function authorize()
    {
        
        $this->sessionStorage->start();
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
    
}

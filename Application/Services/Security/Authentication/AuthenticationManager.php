<?php

namespace Services\Security\Authentication;
use Services\Security\Interfaces\UserInterface;
use Services\Security\Interfaces\AuthenticatorInterface;
use Services\Security\Interfaces\SessionStorageInterface;

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */

/**
 * @category MED CRM
 */

class AuthenticationManager {
    
    private $authenticator;
    private $sessionStorage;
    
    public function __construct(AuthenticatorInterface $authenticator, SessionStorageInterface $sessionStorage)
    {
        $this->authenticator = $authenticator;
        $this->sessionStorage = $sessionStorage;
    }
    
    /**
     * 
     * @param string $login
     * @param string $password
     * @param integer $domen
     * @return boolean|UserInterface
     * @throws UnexpectedValueException
     */
    public function authenticate($login, $password, $domen=null)
    {
        
        $user = $this->authentificator->authentificate($login, $password, $domen);
        if(false === $user || !$user->isExists()){
            return false;
        }
        
        if(!$result instanceof UserInterface){
            throw new \UnexpectedValueException ('Метод должен возвращать false или Services\Security\Interfaces\UserInterface');
        }
                    
        
        $this->sessionStorage->setLogin($result->getLogin());        
        $this->sessionStorage->setDomen($result->getDomen());        
        
        return $result;
        
    }
}

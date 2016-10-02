<?php

namespace Services\Security\Authorization;

use Services\Security\Interfaces\UserRepositoryInterface;
use Services\Security\Interfaces\UserInterface;
use Services\Security\Authorization\AuthorizationManagerAbstract;
use SessionHandlerInterface;

use UnexpectedValueException;

/**
 *
 * @author FedyakinAS
 */
class DBAuthorizationManager extends AuthorizationManagerAbstract{                      
    
    /** @var UserRepositoryInterface */
    private $userRepository;
    
    /** @var integer */
    private $algo;
    
    
    public function __construct(        
        SessionHandlerInterface $sessionStorage,
        UserRepositoryInterface $userRepository,
        $algo = PASSWORD_DEFAULT
    ){
        parent::__construct($sessionStorage);
        
                        
        $this->userRepository = $userRepository;
        $this->algo = $algo;
    }
    
    /**
     * 
     * @param type $login
     * @param type $password
     * @return boolean|UserInterface
     * @throws UnexpectedValueException
     */
    public function authentificate($login, $password) 
    {
        
        $user = $this->userRepository->getUserByLogin($login);
        if(!$user instanceof Services\Security\Interfaces\UserInterface){
            throw new UnexpectedValueException('userRepository must return Services\Security\Interfaces\UserInterface.');
        }
        
        if(!$user->isExists() || $user->getLogin() !== $login){
            return false;
        }
        
        if(!password_verify($password , $user->getPasswordHash())){
            return false;
        }
        
        return $user;
    }
    
    public function authorize(UserInterface $user) {
        ;
    }
}

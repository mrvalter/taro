<?php

namespace Kernel\Services\Security\Authentication;
use Kernel\Services\Security\Interfaces\UserRepositoryInterface;
use Kernel\Services\Security\Interfaces\AuthenticatorInterface;
/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */

/**
 * @category MED CRM
 */
class DbAuthenticator implements AuthenticatorInterface {
        
    public function __construct(UserRepositoryInterface $userRepository)
    {        
                                
        $this->userRepository = $userRepository;        
    }
    
    /**
     * 
     * @param string $login
     * @param string $password
     * @domen integer $domen
     * @return boolean|UserInterface
     * @throws UnexpectedValueException
     */
    public function authenticate($login, $password, $domen = null) 
    {
        
        $user = $this->userRepository->getUserByLogin($login, $domen);
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
}

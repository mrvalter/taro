<?php

namespace Classes;

use Services\Security\Interfaces\UserRepositoryInterface;
use Services\Security\Interfaces\UserInterface;
use Classes\User;
/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */

/**
 * @category MED CRM
 */
class UserRepository implements UserRepositoryInterface{
    
    
    /**
     * 
     * @param string $login
     * @param mixed $domen
     * @return UserInterface
     */
    public function getUserByLogin($login, $domen = '') 
    {
        
        return new User(['id'=>1,'login'=>'sworion', 'domen'=>'1', 'password'=>'ghbdtn']);
    }
}

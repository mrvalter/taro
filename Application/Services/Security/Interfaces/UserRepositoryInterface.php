<?php
namespace Services\Security\Interfaces;
use Services\Security\Interfaces\UserInterface;

interface UserRepositoryInterface {
    
    /**    
     * @param string $login
     * @return UserInterface
     */
    public function getUserByLogin($login, $domen='');
    
}

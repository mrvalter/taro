<?php
namespace Kernel\Services\Security\Interfaces;
use Kernel\Services\Security\Interfaces\UserInterface;

interface UserRepositoryInterface {
    
    /**    
     * @param string $login
     * @return UserInterface
     */
    public function getUserByLogin($login, $domen='');
    
}

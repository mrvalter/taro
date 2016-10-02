<?php
namespace Services\Security\Interfaces;

interface AutorizationManagerInterface {
    
    
    /**
     * 
     * @param UserInterface $user
     * @return boolean
     */
    public function authorize(UserInterface $user);
    
    /**
     * 
     * @param string $login
     * @param string $password
     * @return UserInterface|false
     */
    public function authentificate($login, $password);           
    
}

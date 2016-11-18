<?php
namespace Kernel\Services\Security\Interfaces;
/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */

/**
 * @category MED CRM
 */
interface AuthenticatorInterface {
    
    /**
     * 
     * @param string  $login
     * @param string  $password
     * @param integer $domen
     * @return boolean|UserInterface
     * 
     */
    public function authenticate($login, $password, $domen=null);
    
}

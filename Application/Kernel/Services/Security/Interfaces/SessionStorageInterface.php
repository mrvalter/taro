<?php
namespace Kernel\Services\Security\Interfaces;

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */

/**
 * @category MED CRM
 */
interface SessionStorageInterface {
    
    
    /** @return string */
    public function getLogin();
    
    /** @return integer*/
    public function getDomen();
    
    /** 
     * @param string $login
     * @return SessionStorageInterface 
     */
    public function setLogin($login);
    
    /**
     * @param string $domen 
     * @return SessionStorageInterface
     */
    public function setDomen($domen);
    
    
    /** @return boolean */
    public function isAuthorized();
    
    
    /** @return SessionStorageInterface */
    public function start();
    
}

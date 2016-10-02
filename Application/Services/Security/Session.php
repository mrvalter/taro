<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services\Security;
/**
 * @category MED CRM
 */
class Session {
	
    protected $login;
    protected $domen;
	
    public function __construct($login, $domen)
    {
        
        $this->login = $login;
        $this->domen = $domen;
    }
	
    public function getLogin()
    {
        
        return $this->login;
    }
   
   public function getDomen()
    {
       
        return $this->domen;
    }
   
    public function start()
    {
        
        session_start();		
    }   
	
    public function setNotWrite()
    {
        
        return $this;
    }
	
    public function isDestroy()
    {
        
       return false;
    }
}

<?php

namespace Kernel\Services\Security\SessionStorage;
use Kernel\Services\Security\Interfaces\SessionStorageInterface;

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */

/**
 * @category MED CRM
 */
class NativeSessionStorage implements SessionStorageInterface{
	
    protected $session_id;
    protected $sessionStarted;
    protected $login;
    protected $domen;
    protected $isAuthorized;    
	
    public function __construct($login='', $domen='')
    {        
        $this->login = $login;
        $this->domen = $domen;
        $this->authorize();
        
        
    }	    
    
    public function getLogin()
    {
        
        return $this->login;
    }
   
    public function getDomen()
    {
       
        return $this->domen;
    }
   
    public function setLogin($login)
    {
        
        $this->login = $login;
        $this->authorize();
        return $this;
    }
   
    public function setDomen($domen)
    {
       
        $this->domen=$domen;
        $this->authorize();
        return $this;
    }
    
    public function setWriteMode($mode=false)
    {        
        return $this;
    }
    
    /**
     * 
     * @return SessionStorageInterface
     */
    public function authorize()
    {
        
        if(!$this->login || !$this->domen){
            $this->isAuthorized = false;
        }else{
            $this->isAuthorized = true;
        }
        
        return $this;
    }
    
    /**
     * 
     * @return $this
     */
    public function start()
    {
        if (PHP_SESSION_NONE === session_status()) {
            session_start();			
        }
        
		$this->session_id = session_id();
        $this->sessionStarted = true;
        
        return $this;
    }   	    
    
    public function isAuthorized()
    {
        return $this->isAuthorized;
    }
}

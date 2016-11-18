<?php

namespace Classes;

use Kernel\Services\Security\Interfaces\UserInterface;

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */

/**
 * @category MED CRM
 */
class User implements UserInterface {
    
    
    private $id;
    private $login;
    private $password;
    private $domen;
    
    public function __construct($params) 
    {
        $this->id = isset($params['id']) && is_numeric($params['id']) && $params['id']>0? $params['id'] : null;
        $this->login = isset($params['login'])? trim($params['login']) : '';
        $this->password = isset($params['password'])? trim($params['password']) : '';
        $this->domen = isset($params['domen'])? trim($params['domen']) : '';
        
    }
    
    public function getId() 
    {
        return $this->id;
    }
    
    public function getPasswordHash() 
    {
        return $this->password;
        
    }
    
    public function getLogin()
    {
        return $this->login;
        
    }
    
    public function getDomen()
    {
        
        return $this->domen;
    }
    
    public function isExists() 
    {        
        
        return null !== $this->id;
    }
}

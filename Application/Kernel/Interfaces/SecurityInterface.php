<?php

namespace Kernel\Interfaces;
use Kernel\Services\Security\Interfaces\UserInterface;


interface SecurityInterface {
            
    /**
     * @param string $string
     * @return string
     */
    public function createPasswordHash($string);
    
    /**
     * 
     * @param string $password
     * @param string $hash
     */
    public function isValidPasswordHash($password, $hash);
    
    
    public function authorize();
}

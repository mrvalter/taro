<?php

namespace Kernel\Services\Security\Interfaces;

interface UserInterface {
    
    /** @return integer */
    public function getId();
    
    /** @return string */
    public function getLogin();
    
    /** @return integer */
    public function getDomen();
    
    /** @return string */
    public function getPasswordHash();
    
    /** @return boolean */
    public function isExists();
    
}

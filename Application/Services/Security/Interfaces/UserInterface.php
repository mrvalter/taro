<?php

namespace Services\Security\Interfaces;

interface UserInterface {
    
    /** @return integer */
    public function getId();
    
    /** @return string */
    public function getLogin();
    
    /** @return string */
    public function getPasswordHash();
    
    /** @return boolean */
    public function isExists();
    
}

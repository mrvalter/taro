<?php

namespace Kernel\Services\Security\Interfaces;

interface MenuCollectionInterface extends \Iterator{
        
    public function getById($id): MenuItemInterface;
    public function getByUrl(string $url): MenuItemInterface;
    public function setSelectedUrl();    
            
}

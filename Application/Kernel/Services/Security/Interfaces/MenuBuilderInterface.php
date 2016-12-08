<?php
namespace Kernel\Services\Security\Interfaces;


interface MenuBuilderInterface {
        
    public function getMenuCollection(): MenuCollectionInterface;
    
}

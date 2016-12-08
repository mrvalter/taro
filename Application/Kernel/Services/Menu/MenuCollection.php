<?php

namespace Kernel\Services\Menu;
use Kernel\Classes\Types\ObjectsCollection;
use Kernel\Services\Security\Interfaces\{MenuCollectionInterface, MenuItemInterface};

class MenuCollection extends ObjectsCollection implements MenuCollectionInterface{
	
	
    public function getById($id): MenuItemInterface
	{
		return parent::getById($id);
	}
	
    public function getByUrl(string $url): MenuItemInterface
	{
		
	}
	
    public function setSelectedUrl()
	{
		
	}
	
    
}

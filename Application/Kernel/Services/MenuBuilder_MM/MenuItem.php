<?php
namespace Kernel\Services\MenuBuilder_MM;
use Kernel\Services\Security\Interfaces\{MenuCollectionInterface, MenuItemInterface, UserInterface};

class MenuItem implements MenuItemInterface{    	
	
    private $id;
    private $url;
	private $name;
	private $type;	
    private $parent;
    private $childs;
    
    public function __construct($params)
    {
        $this->id   = $params['id']?? null;
		$this->url  = $params['url']?? null;
		$this->name = $params['name']?? null;
		$this->type = $params['type']?? null;
		$this->parent = null;
		$this->childs = new MenuCollection();
		
    }
	
	public function isExists(): bool
	{
		return $this->id ? true : false;
	}
	
	public function getName(): string
	{
		return $this->name;
	}
	
	public function getUrl(): string
	{
		return $this->url;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getRightsForUser(UserInterface $user)
	{
		
		
	}
	
	public function getParent(): MenuItemInterface
	{
		
		
	}
	
	public function getChilds(): MenuCollectionInterface
	{
		return $this->childs;
	}
	
	public function setParent(MenuItemInterface $menuItem)
	{
		$this->parent = $menuItem;
	}
	
	public function addChild(MenuItemInterface $menuItem)
	{
		$this->childs->push($menuItem);
	}
	
	public function hasParent(): bool
	{
		return null !== $this->parent;
	}
	
	public function hasChilds(): bool
	{
		$this->childs->rewind();
		return $this->childs->valid();
	}
}

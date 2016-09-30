<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services\Menu;

/**
 * @category MED CRM
 */
class TreeMenuCollection extends MenuCollection{
	
	
    public function __construct($array=array())
	{
		if(isset($array[0])){						
			$result=[];
			foreach($array as $i=>$menuItem){				
				$clItem = clone $array[$i];		
				$result[] = $clItem;				
			}		

			$moveIdx=[];	

			foreach($result as $num=>$menuItem){
				foreach($result as $i=>$parent){
					if($parent->id == $menuItem->parent_id){
						$moveIdx[] = $num;
						$result[$i]->addChild($menuItem);
					}
				}
			}		

			foreach($moveIdx as $num){		
				unset($result[$num]);
			}
			
			
			foreach($result as $item){
				$this->items[] = $item;
			}			
			
			unset($items);
		}		
	}
	
	/**
	 * 
	 * @param string $path
	 * @return mixed menuItem || null
	 */
	public function getItemByPath($path)
	{						
		if(!isset($this->items[0])){
			return null;
		}
		
		foreach($this->items as $menuItem){
			if(strtolower($menuItem->menu_url) == strtolower($path)){
				return $menuItem;				
			}						
			
			$childItem = $menuItem->getChilds()->getItemByPath($path);
			if(null !== $childItem){
				return $childItem;
			}
		}				

		
		return null;
						
	}
	
	/**
	 * Возвращает меню по id
	 * @param integer $menuId
	 * @return null|MenuItem
	 */
	public function getItemById($menuId)
	{
		if(!isset($this->items[0])){
			return null;
		}
		
		foreach($this->items as $menuItem){
			if($menuItem->id == $menuId){
				return $menuItem;
			}
			
			$inChilds = $menuItem->getChilds()->getItemById($menuId);			
			if(null !== $inChilds){
				return $inChilds;
			}
		}
		
		return null;
	}
	
	public function getForMenu()
	{		
		$result = [];
		foreach($this->items as $i=>$menuItem){
			$item = $menuItem->getForMenu();
			if(null === $item){
				continue;
			}
			$result[] = $items;
		}
		
		$collection = new TreeMenuCollection();
		//var_dump($collection->setTreeItems($result));
		
	}	
	
	public function setTreeItems($items)
	{
		$this->items = $items;
	}
	
	
	public function setCurrentPath($path) {
		
		if(!sizeof($this->items)){
			false;
		}
						
		foreach($this->items as $item){
			if($item->menu_url == $path){
				$item->setOpened();
				return true;
			}
		}
				
		foreach($this->items as $item){
			if($item->getChilds()->setCurrentPath($path)){
				$item->setOpened();
				return true;
			}
		}
		
	}
}

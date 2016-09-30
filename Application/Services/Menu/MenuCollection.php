<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services\Menu;

/**
 * @category MED CRM
 */
class MenuCollection implements \Iterator{
	
	protected $items = array();	
	protected $currentPath;
	private $treeCollection;

    public function __construct($array=array())
    {			
		if($array instanceof MenuItem){
			$array = [$array];
		}
		
        if (is_array($array) && isset($array[0])) {
			foreach($array as $item){
				if($item instanceof MenuItem){						
					$this->items[] = $item;
				}
			}            
        }
		
		$this->treeCollection = new TreeMenuCollection();		
    }					
	
	
	public function setCurrentPath($path)
	{
		if(!sizeof($this->items)){
			return false;
		}
		
		$this->currentPath = $path;
		
		foreach($this->items as $menuItem){
			if($menuItem->menu_url == $path){
				$menuItem->setEnabled();				
				return true;
			}						
		}
				
		
		return false;
	}
	
	/**
	 * Возвращает дерево колекцию ItemMenu
	 * @return \Services\Menu\MenuItem
	 */	
	public function getItemByPath($path)
	{								
		foreach($this->items as $menuItem){
			if(strtolower($menuItem->menu_url) == strtolower($path)){
				return $menuItem;				
			}			
		}
		
		return null;
						
	}
	
	/**
	 * 
	 * @param integer $menuId
	 * @return TreeMenuCollection
	 */
	public function getTree()
	{					
		if(!$this->treeCollection->valid() && isset($this->items[0])){			
			$this->treeCollection = new TreeMenuCollection($this->items);
			$this->treeCollection->setCurrentPath($this->currentPath);
			return $this->treeCollection;
			/*var_dump($col);
			var_dump($this->items);
			return $col;*/
		}		
		return $this->treeCollection;		
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
		}
		
		return null;
	}		
	
    public function rewind()
    {
        reset($this->items);
		return $this;
    }
  
	/**
	 * 
	 * @return MenuItem
	 */
    public function current()
    {
        $var = current($this->items);        
        return $var;
    }
  
    public function key() 
    {
        $var = key($this->items);        
        return $var;
    }
  
	/**
	 * 
	 * @return MenuItem
	 */
    public function next() 
    {
        $var = next($this->items);        
        return $var;
    }
  
    public function valid()
    {
		//$this->rewind();
        $key = key($this->items);
        $var = ($key !== NULL && $key !== FALSE);        
        return $var;
    }
	
	public function push(MenuItem $menu)
	{
		if(null === $this->getItemById($menu->id)){
			$this->items[] = $menu;					
		}		
		
		return $this;
	}
	
	public function setLevel($level)
	{
		$this->level = $level;
		if(!$this->valid()){
			return $this;
		}
		foreach($this->items as $item){
			$item->setLevel($level);
		}
		$this->rewind();
	}	
	
	
}

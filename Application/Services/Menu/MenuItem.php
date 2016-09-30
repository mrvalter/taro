<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */

namespace Services\Menu;
/**
 * @category MED CRM
 */
class MenuItem {
    use \Getter;
    
    private $id;
    
    private $parent_id;
    private $menu_order;
    private $menu_type;
    private $menu_url;    
	private $Rights;    		
    private $_bundle;
	private $MLEVEL;
	private $show_menu;
	private $enabled = false;
	private $opened = false;
	private $childs=null;
           
	public function __construct()
	{
		$this->childs = new TreeMenuCollection();
	}	                      
    
	public function __clone()
	{
		$this->childs = new TreeMenuCollection();
	}
		
	/**
	 * 
	 * @return MenuCollection
	 */
    public function getChilds()
    {
        return $this->childs;
    }
	
	public function getForMenu()
	{
		if(!$this->show_menu || !$this->getRight()){
			return null;
		}
		
		$menuItem = clone $this;
		
		foreach($this->childs as $child){
			$fmChild = $child->getForMenu();
			if(null !== $fmChild){
				$menuItem->addChild($fmChild);
			}
		}
		return $menuItem;
	}
	
	public function getLevel()
	{
		return $this->MLEVEL;
	}
	
	/**
	 * Возвращает метку прав
	 * @return string
	 */
	public function getRight()
	{
		return $this->Rights;
	}
		
	/**
	 * Устанавливает Дочерние меню
	 * @param \Services\Menu\MenuCollection $childs
	 * @return \Services\Menu\MenuItem
	 */
	public function setChilds(TreeMenuCollection $childs)
	{		
		$this->childs = $childs;
		return $this;
	}
	/**
	 * 
	 * @param string $right
	 * @return \Services\Menu\MenuItem
	 */
	public function setRight($right)
	{
		$this->Rights = $right;
		return $this;
	}
	
	/**
	 * 
	 * @param type $bundle
	 * @return \Services\Menu\MenuItem
	 */
	public function setBundle($bundle)
    {
        $this->_bundle = $bundle;
        return $this;
    }  
	
	/**
	 * Устанавливает левел 
	 * @param integer $level
	 * @return \Services\Menu\MenuItem
	 */
	public function setLevel($level)
	{
		if(!is_numeric($level)){
			return $this;
		}
		$this->MLEVEL = $level;
		
		if(!$this->childExists()){
			return $this;
		}
		
		$this->childs->setLevel($level+1);
		
		return $this;
	}
	
	/**
	 * Проверяет есть ли дочерние меню
	 * @return boolean
	 */
	public function childExists($forShowMenu=false)
    {		
		$childExist = $this->childs->rewind()->valid();
		if(!$forShowMenu){
			return $childExist;
		}
		
		if($childExist){
			foreach($this->childs as $child){
				if($child->isForShowing()){
					return true;
				}
			}
		}
		
    }
	
	/**
	 * Добавляет дочерний элемент меню
	 * @param \Services\Menu\MenuItem $menu
	 * @return \Services\Menu\MenuItem 
	 */
	public function addChild(MenuItem $menu)
    {
        $this->childs->push($menu);
		return $this;
    }
	
	public function setEnabled()
	{
		$this->enabled = true;
	}
	
	public function setOpened()
	{
		$this->opened = true;
	}
		
	public function isEnabled()
	{
		return (bool)$this->enabled;
	}
	
	public function isOpened()
	{
		return (bool)$this->opened;
	}
	
	public function isMenu()
	{
		return $this->menu_type == 'PageMenu';
	}
	
	public function isForShowing()
	{
		return $this->isMenu() && $this->isShow() && $this->getRight();
	}
	
	public function isShow()
	{
		return (bool)$this->show_menu;
	}
	
	/**
	 * Делает правильный URL  подставляя текущий бандл
	 * @return string
	 */
	public function makeUrl()
    {
		if(trim($this->menu_url) == ''){
			return '';
		}
		if($this->menu_url == '/'){
			$this->menu_url.='Index';
		}
        return '/'.$this->_bundle.$this->menu_url;
    }
}

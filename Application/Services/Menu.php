<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services;

use Services\Menu\MenuCollection as MenuCollection;
use Services\Menu\TreeMenuCollection as TreeMenuCollection;
use Classes\ADUser as ADUser;

/**
 * @category MED CRM
 */
class Menu {
    	
    private $db;
    private $userMenu = null;
    private $currentPath;	
	private $user;
	private $bundle; 
	private $rights;
    
    public function __construct(DB $db, ADUser $user, $currentPath='')
    {
        $this->db = $db->getDBConn('db');
		$this->user = $user;
		$this->currentPath = $currentPath;
    }		
	
	public function getBreadCrumbs()
	{							
		$this->buildMenu();
		$item = $this->userMenu->getItemByPath($this->currentPath);
		if(null === $item){
			return [];
		}

		$parent_id = $item->parent_id;		
		$find = true;
		$breadCrumbs = [];
		while($parent_id && $find){
			$find = false;
			$one = [];
			foreach($this->userMenu as $menu){				
				if($menu->id == $parent_id){					
					$parent_id = $menu->parent_id;
					$find = true;
					$url = $menu->makeUrl();
					if(!$menu->isMenu() || !$menu->getRight() || !$menu->isShow() || $menu->$url=='') {
						break;
					}
					$one['link'] = $url;
					$one['name'] = $menu->menu_name;
					array_unshift($breadCrumbs, $one);
				}
			}
		}				
		array_push($breadCrumbs, ['link'=>$item->makeUrl(), 'name'=>$item->menu_name]);
		return $breadCrumbs;
	}
	/**
	 * 
	 * @param ADUser $user
	 * @param string $currentPath
	 * @param string $bundle
	 */
	public function buildMenu()
	{	
		if($this->userMenu){
			return $this;
		}
		/* получаем все меню */
		$items = $this->getMenuListByProcedure($this->user, 0, 0);
		
		usort($items, array($this, 'itemsSort'));
		
		$this->userMenu = new MenuCollection($items);
		$this->userMenu->setCurrentPath($this->currentPath);
		return $this;
	}
	
	private function itemsSort(Menu\MenuItem $a, Menu\MenuItem $b)
	{
		$al = (int)$a->menu_order;
        $bl = (int)$b->menu_order;
		
		if ($al == $bl) {
            return 0;
        }
		
        return ($al > $bl) ? +1 : -1;
	}
	
	public function checkCurrentPath()
	{		
		
		if(!$this->currentPath){
			return false;
		}
				
		$sql = 'SELECT COUNT(*) as count FROM `Page_Menu` WHERE menu_url=:menu_url';
		$dbo = $this->db->getPDO();
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':menu_url', $this->currentPath, \PDO::PARAM_STR);
		$stmt->execute();		
		$result = $stmt->fetch();
		
		if($result['count'] == 1){
			return true;
		}				
		
		return false;
		
	}
	
	public function getCurrentPath()
	{
		return $this->currentPath;
	}
	
	public function setCurrentPath($currentPath)
	{
		$this->currentPath = $currentPath;
	}		
	
	public function setBundle($bundle)
	{
		$this->bundle = $bundle;
	}
	
	/**
	 * Возвращает объекты меню
	 * @param bool $onlyPageMenu Возвращать только с параметром Page_Menu
	 * @param bool $makeTree Вернуть дерево элементов
	 * @return array MenuItem
	 */
    public function getItems($onlyPageMenu = true, $onlyShowInMenu=true)
    {    
        $where= [];
		
		if($onlyPageMenu){
			$where[] = 'menu_type = "PageMenu"';
		}
		
		if($onlyShowInMenu){
			$where[] = 'show_menu = 1';
		}
		
        $sql = 'SELECT * FROM Page_Menu'.(isset($where[0])? ' WHERE '.implode(' AND ', $where) : '');
				
        $pdo = $this->db->getPDO();
        $stmt = $pdo->query($sql);  
        $items = $this->makeCollection($stmt)->getTree();
		foreach($items as $item){
			$item->setLevel(1);
		}
		$items->rewind();

		return $items;
    }			    
    
	
	public function getMenu()
	{
		$this->buildMenu();
		
		if($this->userMenu === null){
			return new TreeMenuCollection();
		}					
					
		$menu = $this->userMenu->getTree();				
				
		return $menu;
	}	         	
	
	public function getItemByPath(ADUser $user, $path)
	{	
		if($this->userMenu){
			return $this->userMenu->getTree()->getItemByPath($path);	
		}
		
		$sql = 'SELECT id
				FROM  `Page_Menu` 
				WHERE `menu_url` =  :path
			';
		
        $stmt = $this->db->getPDO()->prepare($sql);
		$stmt->bindParam(':path', $path, \PDO::PARAM_STR);
        $stmt->execute();
		$result = $stmt->fetch();
		
        if(!$result){
			return null;
		}
				
		$menuId = $result['id'];
		$items = $this->getMenuListByProcedure($user, $menuId, 0);
				
		$collection = new MenuCollection($items);		
		return $collection->getTree()->getItemById($menuId);
		
	}
		
    public function update($id, $row, $value)
    {
        if($id){
            $sql = "UPDATE Page_Menu SET `$row` = :value WHERE id=:id";
            $stmt = $this->db->getPDO()->prepare($sql);
            $stmt->execute([':value'=>$value, ':id'=>$id]);
        }else{
            $sql = "INSERT INTO Page_Menu SET `$row` = :value";
            $stmt = $this->db->getPDO()->prepare($sql);
            $stmt->execute([':value'=>$value]);
        }
                
        
        return true;
    }
	
	public function add($rows){
		
		$sql = "INSERT INTO `Page_Menu` SET "
				. "`menu_name` = :menu_name, "
				. "`menu_url` = :menu_url, "
				. "`menu_type`=:menu_type, "
				. "`parent_id`=:parent_id ";
		
		$stmt = $this->db->getPDO()->prepare($sql);
		$stmt->bindParam(':menu_name', $rows['menu_name'], \PDO::PARAM_STR);
		$stmt->bindParam(':menu_url',  $rows['menu_url'],  \PDO::PARAM_STR);
		$stmt->bindParam(':menu_type', $rows['menu_type'], \PDO::PARAM_STR);
		$stmt->bindParam(':parent_id', $rows['parent_id'], \PDO::PARAM_INT);
		$stmt->execute();
		return true;
	}
	
    
    public function delete($id)
    {
        $sql = "DELETE FROM Page_Menu WHERE id=:id";
        $stmt = $this->db->getPDO()->prepare($sql);
        $stmt->execute([':id'=>$id]);
    }
    
	
	/**
	 * 
	 * @param \PDOStatement $stmt
	 * @return MenuCollection
	 */
	public function makeCollection(\PDOStatement $stmt)
    {
        $items = self::makeMenuCollection($stmt);
				
		if($items->valid()){
			foreach($items as $menuItem){
				$menuItem->setBundle($this->bundle);
			}
			$items->rewind();
		}
		
		return $items;
    }
	
	/**
	 * 
	 * @param type $stmt
	 * @return Services\Menu\MenuCollection
	 */
	public static function makeMenuCollection($stmt)
	{
		$items = $stmt->fetchAll(\PDO::FETCH_CLASS, 'Services\Menu\MenuItem');				
		
		return $items? new MenuCollection($items) : new MenuCollection();		
	}		
	
	private function getMenuListByProcedure(\Classes\ADUser $user, $menuId, $onlyFirst=1)
	{			
		$pdo = $this->db->getPDO();								
		$st = $pdo->query("call PageMenu_Levellist('".$user->sAMAccountName."', ".$user->Domen.", $menuId, $onlyFirst, 0)");
		
		$items = $st->fetchAll(\PDO::FETCH_CLASS, 'Services\Menu\MenuItem');			
		if(isset($items[0])){		
			foreach($items as $key=>$menuItem){				
				$items[$key]->setBundle($this->bundle);
			}			
		}											
		
		return $items;		
	}
	
	private function getExistRightById($menuId)
	{
		if($menuId && isset($this->rights[0])){
			foreach($this->rights as $right){
				if($right->id == $menuId){
					return $right;
				}
			}
		}
		
		return null;
	}
	
	
    
    
    
}

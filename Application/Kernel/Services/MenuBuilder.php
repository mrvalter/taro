<?php

namespace Kernel\Services;
use Kernel\Classes\Repository;
use Kernel\Services\Menu\{MenuCollection, MenuItem};
use Kernel\Services\Security\Interfaces\{MenuBuilderInterface, MenuCollectionInterface};

class MenuBuilder extends Repository implements MenuBuilderInterface{	    
    
    private $menuCollection;
		
	
    public function getMenuCollection(): MenuCollectionInterface
    {        
        $pdo = $this->getPDOFrom('db');        
		$sql = 'SELECT * FROM PageMenu ORDER BY parent_id ASC';
		$stmt = $pdo->query($sql);
		
		$menuTree = new MenuCollection();		
		$menuList = new MenuCollection();
		
		while ($row = $stmt->fetch(\PDO::FETCH_LAZY)){
			$menuItem = $this->_buildMenu($row);
			$menuList->push($menuItem);
			if($row->parent_id){
				$parent = $menuList->getById($row->parent_id);
				$parent->addChild($menuItem);
				$menuItem->setParent($parent);				
			}else{
				$menuTree->push($menuItem);
			}
		}
		
		return $menuTree;
    }
	
	private function _buildMenu(\PDORow $row)
	{
		return new MenuItem((array)$row);
	}
}

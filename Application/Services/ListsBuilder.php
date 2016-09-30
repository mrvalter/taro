<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services;
/**
 * @category MED CRM
 */
class ListsBuilder {
	
	use \Getter;
	
	private $rows;			
	
	private $canDelete = false;
	private $canUpdate = false;
	private $canAdd = false;
	
	private $showcheckAll = false;
	
	private $name;
	private $data;
	private $names;
	
	private $addParams = [];
	private $deleteParams = [];
	private $updateParams = [];
	private $types = [];
	
	public function __construct($name='')
	{
		$this->name = $name? $name : md5(rand(1,500));
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getRows()
	{
		return $this->rows;
	}			
	
	
	public function setData(array $data)
	{
		$this->data = $data;
		return $this;
	}
	
	public function setType(array $types)
	{
		$this->types = $types;
	}
	
	public function canAdd()
	{
		$this->canAdd = true;
		return $this;
	}
	public function canUpdate()
	{
		$this->canUpdate = true;
		return $this;
	}
	public function canDelete()
	{
		$this->canDelete = true;
		return $this;
	}
	
	public function showCheckAll()
	{
		$this->showcheckAll = true;
		return $this;
	}
	
	
	public function setNames(array $names)
	{
		$this->names = $names;
		return $this;
	}
	
	public function addParams(array $array)
	{
		$this->addParams = $array;
		return $this;
	}
	
	public function deleteParams(array $array)
	{
		$this->deleteParams = $array;
		return $this;
	}
	
	public function updateParams(array $array)
	{
		$this->updateParams = $array;
		return $this;
	}
	
	public function operationExists()
	{		
		return $this->canDelete || $this->canUpdate;
	}
	public function render($template)
	{		
		$view  = new View();
		$view->addTemplatePath(dirname(__FILE__).'/ListsBuilder/view');
		
		
		//return $view->setContentHTML('HTML CONTENT LISTS BUILDER');
	}
	
	public function showJs()
	{		
		include dirname(__FILE__).'/ListsBuilder/view/js.php';		
	}
	
	public function showContent()
	{	
        $view = new View();
        $view->addTemplatePath(dirname(__FILE__).'/ListsBuilder/view/');
        echo $view->render('crm_lists',
            [
                'canAdd'=>$this->canAdd,
                'canDelete'=>$this->canDelete,
                'canUpdate'=>$this->canUpdate,
                'showcheckAll'=>$this->showcheckAll,
                'names'=>$this->names,
                'data' => $this->data,
                'operationExists'=>$this->operationExists(),
                'types'=>$this->types
            ]
        )->getContentHTML();
        return;
		include dirname(__FILE__).'/ListsBuilder/view/crm_lists.php';
	}
	
	public function createRow($item)
	{
        $view = new View();
        $view->addTemplatePath(dirname(__FILE__).'/ListsBuilder/view/');
        
		return $view->render('crm_lists_one_row',
            [
                'canAdd'=>$this->canAdd,
                'canDelete'=>$this->canDelete,
                'canUpdate'=>$this->canUpdate,
                'showcheckAll'=>$this->showcheckAll,
                'names'=>$this->names,
                'data' => $this->data,
                'operationExists'=>$this->operationExists(),
                'types'=>$this->types,
                'item' =>$item
            ]
        )->getContentHTML();
	}
		
}

<?php
namespace Kernel\Classes;

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */

use \Services\Interfaces\ViewInterface as ViewInterface;
use \Services\Menu\MenuCollection as MenuCollection;
/**
 * @category MED CRM
 */
class Widget extends PublicController{
	
	public function __construct(ViewInterface $view)
	{
		parent::__construct($view);
		$this->setLayout('ajax.layout');		
	}
	
	public function getRights()
	{
		return new MenuCollection();
	}	   	
	
	protected function checkRightW($optionName='')
	{
		return false;
	}
		
}

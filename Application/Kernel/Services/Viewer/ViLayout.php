<?php
namespace Kernel\Services\Viewer;

use Kernel\Classes\Types\ObjectsCollection;

/**
 * Description of Layout
 *
 * @author sworion
 */
class ViLayout {
	
	private $blocks=[];		
	private $layout = '';
	private $context='';
	
	public function addBlock(ViBlock $block): self
	{
		if(isset($this->blocks[$block->name]) && $block->isAddContent){
			$this->blocks[$block->name]->insertBefore($block->content);			
		}else{
			$this->blocks[$block->name] = $block;
		}
		
		return $this;
	}		
	
	public function setLayout($layout)
	{
		$this->layout = $layout;
	}
	
	public function render()
	{
		
		
	}
}

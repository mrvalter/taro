<?php
namespace Kernel\Services\Viewer;


class ViBlock {
	use \Kernel\Classes\Getter;
	
	private $name;
	private $content;
	private $isAddContent = false;
	
	public function __construct($name, $content, $isAddContent = false) {
		
		$this->name = $name;
		$this->content = $content;
		$this->isAddContent = $isAddContent;
	}
	
}

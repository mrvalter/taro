<?php
namespace Kernel\Services\Viewer;

use Kernel\Interfaces\ViewInterface;
use Kernel\Services\FileDataStorage;

class Vi implements ViewInterface{
	
	const fileExtension = '.php';						
	private $pathes;				
	private $viLayout;
	
	public function __construct($params)
	{	
		$this->pathes['simple'] = [];
		
		if(isset($params['layoutPath'])){
			$this->addTemplatePath($params['layoutPath'], 'layouts');
		}		
		
		$this->viLayout = new ViLayout();
	}	    
    
	/**
	 * 
	 * @param string $template
	 * @param string $namespace
	 * @return string
	 * @throws \ViException
	 */
    private function getFileByTemplateName(string $template, string $namespace=''): string
	{						
		
		if($namespace && !isset($this->pathes['namespace'][$namespace])){
			throw new \ViException("Не назначен путь для неймспейса $namespace");
		}
		
		$template .= self::fileExtension;
		$pathes = $namespace ? $this->pathes['namespace'][$namespace] : $this->pathes['simple'];
		
		$file = '';
		foreach($pathes as $path){		
			if(file_exists($path.'/'.$template)){
				$file = $path.'/'.$template;
				break;
			}
		}
		
		if(!$file){
			throw new \ViException("Не найден файл темплейта $template");
		}
		
		return $file;
		
	}
	
    public function getFileExtension(): string
	{
		
		return self::fileExtension;
	}
        
    public function addTemplatePath($path, $namespace = ''): self
	{
		$namespace = trim($namespace);
		
		if(($namespace)){
			$this->pathes['namespace'][strtolower($namespace)][] = $path;
		}else{
			$this->pathes['simple'][] = $path;
		}
		
		return $this;		
	}
	
	public function render(string $template, array &$params=[], string $namespace = ''): string
	{															
		
		$this->createExtendsContent($template, $namespace);
		
		var_dump($this->viLayout);
		die();
	}

	
	
	public function createLayoutObject()
	{
		
	}
	
	private function createExtendsContent(string $template, string $namespace='')
	{
		
		$namespace = strtolower(trim($namespace));		
		$file = file_get_contents($this->getFileByTemplateName($template, $namespace));
		
		$pNamespace = '';
		$pTemplate = '';
		if(preg_match('~{extends([^}]+)}~', $file, $extends)){			
			$templateStr = trim($extends[1]);			
			if(strpos($templateStr, '@')){
				list($pNamespace, $pTemplate) = explode('@', $templateStr);				
			}else{
				$pTemplate = $templateStr;
			}
		}
		
		if(!$pTemplate){
			$this->viLayout->setLayout($file);
			return true;
		}
		
		$blocks = [];
		if(preg_match_all('~{block([^}/]+)}(.*?){\/block}~uis', $file, $blocks)){
			foreach($blocks[1] as $i=>$params){
				$params = trim($params);
				$blockParams = explode(' ', $params);				
				$replace = isset($blockParams[1]) && $blockParams[1] === 'add' ? false : true;
				$block = new ViBlock($blockParams[0], $blocks[2][$i], !$replace);				
				$this->viLayout->addBlock($block);
				
			}
		}
		
		$eblocks = [];
		if(preg_match_all('~{block([^}]+)\/}~uis', $file, $eblocks)){
			foreach($eblocks[1] as $i=>$params){
				$params = trim($params);
				$blockParams = explode(' ', $params);
				$replace = isset($blockParams[1]) && $blockParams[1] === 'add' ? false : true;
				if($replace){
					$block = new ViBlock($blockParams[0], '', !$replace);
					$this->viLayout->addBlock($block);
				}								
			}
		}
		
		return $this->createExtendsContent($pTemplate, $pNamespace);
		
	}
}



function e($string)
{
	return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
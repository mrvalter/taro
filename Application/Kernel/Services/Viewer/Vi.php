<?php
namespace Kernel\Services\Viewer;

use Kernel\Interfaces\ViewInterface;
use Kernel\Services\FileDataStorage;

class Vi implements ViewInterface {
	
	const fileExtension = '.php';
	const defaultLayout = 'default.layout';
	const defaultCharset = 'UTF-8';
	const defaultLang = 'ru';
	
	public $layout;
	public $charset;
	public $lang;
	public static $title;
	
	private $pathes;
	private static $parent;
	private static $blocks;
	private static $blocksBegin;
	
	
	public function __construct($params)
	{
		
		$this->charset = $params['charset']?? self::defaultCharset;
		$this->layout = $params['layout']?? self::defaultLayout;
		$this->lang = $params['lang']?? self::defaultLang;
		if(isset($params['layoutPath'])){
			$this->addTemplatePath($params['layoutPath'], 'layouts');
		}						
	}	    
    
    private function getFileByTemplateName(&$template)
	{
		$namespace = '';
		
		if(substr($template, 0, 1) == '@'){
			$templateParts = explode('/', $template);
			$namespace = substr($templateParts[0], 1);
			$templateName = $templateParts[1].$this->getFileExtension();
		}else {
			$templateName = $template.$this->getFileExtension();
		}
				
		if($namespace && !isset($this->pathes['namespace'][$namespace])){
			throw new \ViException("Не найден путь к шаблону $template");
		}
		
		$file = '';
		foreach($this->pathes['namespace'][$namespace] as $path){
			if(file_exists($path.'/'.$templateName)){
				$file = $path.'/'.$templateName;
				break;
			}
		}
		
		if(!$file){
			throw new \ViException("Не найден файл темплейта $template");
		}
		
		return $file;
		
	}
	
    public function getFileExtension()
	{
		
		return self::fileExtension;
	}
    
    public function getLayout()
	{
		
		return $this->layout;
	}
    
    /**
     * Возвращает название текущей кодировки
     * @return string;
     */
    public function getCharset()
	{
		
		return $this->charset;
	}
            
    /**
     * Возвращает название текущего языка
     * @return string;
     */
    public function getLang()
	{
		
		return $this->lang;
	}
            
    public function setLayout($layoutName)
	{
		$this->layout = $layoutName;
	}
    
    /**
     * Устанавливает кодировку страницы в шапке
     * @param string $charset
     * @return \ViewInterface
     */
    public function setCharset($charset)
	{
		
		$this->charset = $charset;
	}
    
    /**
     * Устанавливает язык страницы в шаблоне.  <html lang="ru">
     * @param string $lang
     * @return \ViewInterface
     */
    public function setLang($lang)
	{
		
		$this->lang = $lang;
	}
    
	
    public function addTemplatePath($path, $namespace = ''): self
	{
		$namespace = trim($namespace);
		
		if(($namespace)){
			$this->pathes['namespace'][$namespace][] = $path;
		}else{
			$this->pathes['simple'][] = $path;
		}
		
		return $this;		
	}
	
	public function render(string $template, array $params=[], string $namespace = ''): string
	{

		$file = $this->getFileByTemplateName($template);		
		ob_start();
		if(!empty($params)){
			extract($params);
		}
		include $file;
		$html = ob_get_contents();		
		ob_clean();
		
		var_dump(self::$blocks);		
		if(self::$parent){			
			$parentTemplate = self::$parent;
			self::$parent = null;
			$this->render($parentTemplate);
		}		

		var_dump(self::$blocks);
		var_dump('dddd');
		return 'ddd';
	}
	
	public static function beginBlock(string $name, bool $includeParent = false)
	{
		self::$blocksBegin[] = [$name, $includeParent];
		ob_start();
		
	}
	
	public static function endBlock()
	{
		$block = array_pop(self::$blocksBegin);
		self::$blocks[$block[0]][] = ['html' => ob_get_contents(), 'parent'=>$block[1]];
		ob_clean();
		
	}
	
	public static function extend($name)
	{
		self::$parent = $name;
	}
	
}


function e($string)
{
	return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
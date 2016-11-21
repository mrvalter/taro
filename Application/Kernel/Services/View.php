<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Kernel\Services;
use Kernel\Interfaces\ViewInterface;

/**
 * Шаблонизатор
 * @category MED CRM
 * 
 * @property-read string $layout Название основного шаблона
 * @property-read string $charset Кодировка страницы
 * @property-read string $lang Язык страницы
 * @property-read string $ext файловое окончание у темплейтов
 * @property-read string $title Тайтл страницы
 * 
 */
class Viewer implements ViewInterface {
    
	private static $_mediaPath = null;
	private static $_globalMediaPath = null;
	
	private static $_headJSScripts = [];
	private static $_headCss = [];
	
    const DEFAULT_EXT = '.php';
    const DEFAULT_LAYOUT = 'default.layout';
    const DEFAULT_CHARSET = 'UTF-8';
    const DEFAULT_LANG = 'ru';
    const DEFAULT_TITLE = ' office.materiamedica.ru ';	
    
    private $_layout;
    private $_charset;
    private $_lang;
    private $_ext;
    
    private $layoutPath;
    private $templatePathes;
    private $mediaPath = '';
	private $globalMediaPath = '/media';
	
    /* web */
    private $_title;
    private $params;
        
    private $contentHTML;
        
            
    
    public function __construct(array $defaults=array())
    {        
        $this->_layout   = isset($defaults['layout'])   ? $defaults['layout']      : self::DEFAULT_LAYOUT;
        $this->_charset  = isset($defaults['charset'])  ? $defaults['charset']     : self::DEFAULT_CHARSET;
        $this->_lang     = isset($defaults['lang'])     ? $defaults['lang']        : self::DEFAULT_LANG;
        $this->_ext      = isset($defaults['ext'])      ? $defaults['ext']         : self::DEFAULT_EXT;
        $this->_title    = isset($defaults['title'])    ? $defaults['title']       : self::DEFAULT_TITLE;
		
		if(null !== self::$_mediaPath){
			$this->mediaPath = self::$_mediaPath;
		}
		
		if(null !== self::$_globalMediaPath){
			$this->globalMediaPath = self::$_globalMediaPath;
		}
    }
    
    public function __toString() {
        return $this->getContentHTML();
    }
    public function __get($name) 
    {
        $name = '_'.$name;
        if(property_exists($this, $name)){
            return $this->$name;
        }
    }
    
    /**
     * Возвращает сгенерированный HTML
     * @param string $template Имя используемого темплейта
     * @param array $params передаваемые параметры
     */
    public function render($template, $PARAMS=array(), $dir=null)
    {                     
        if(sizeof($PARAMS)){
            foreach($PARAMS as $key=>$value){
                if(is_numeric($key) || is_numeric(substr($key, 0, 1))){
                    continue;
                }
                $$key = $value;
            }
        }
        $fileTemplate = null;		
        if(isset($this->templatePathes[0]) && null === $dir){
            foreach($this->templatePathes as $templatePath){
                $file = $templatePath.'/'.$template.$this->getFileExtension();
                if(file_exists($file)){
                    $fileTemplate = $file;
                    break;
                }
            }
        }elseif($dir){
            $file = $dir.'/'.$template.$this->getFileExtension();            
            if(file_exists($file)){
                $fileTemplate = $file;             
            }
        }
        
        if(null === $fileTemplate){			
            throw new \FileNotFoundException('Не найден файл темплейта "'.$template.'"');
        }
                
        ob_start();
        include $fileTemplate;
        $this->contentHTML =  ob_get_clean();
		return $this;
    }
    
    public function renderPage($layout='')
    {		
        $layout = $layout ? $layout : $this->_layout;
        $layoutFile = $this->layoutPath.'/'.$layout.$this->_ext;
        
        if(!file_exists($layoutFile)){
            throw new \FileNotFoundException('Не найден файл Шаблона "'.$layoutFile.'"');
        }             
        ob_start();
		if(sizeof($this->params)){
			foreach($this->params as $key=>$param){
				$$key = $param;
			}
		}
        $CONTENT = $this->contentHTML;
        include $layoutFile;
        $layoutHTML = ob_get_clean();                                
        $this->page = $layoutHTML;
		return $this;
    }
    
    
    public function showHead()
    {
        if(isset(self::$_headJSScripts[0])){
            foreach(self::$_headJSScripts as $script){           
                echo '<script src="'.$script.'" type="text/javascript"></script>'."\r\n";
            }
        }
        
        if(isset(self::$_headCss[0])){
            foreach(self::$_headCss as $script){           
                echo '<link href="'.$script.'" rel="stylesheet">'."\r\n";                
            }
        }
    }
    
    public function getContentHTML()
    {
        return $this->contentHTML;
    }
	
	public function getPage()
	{
		return $this->page;
	}
    
    /**
     * Возвращает окончание файлов шаблонов и темплейтов
     * @return string 
     */
    public function getFileExtension()
    {
        return $this->ext ? $this->ext : self::EXT;
    }
    
    /**
     * Возвращает название используемого шаблона
     * @return string;
     */
    public function getLayout()
    {
        return $this->_layout;
    }
    
    /**
     * Возвращает название текущей кодировки
     * @return string;
     */
    public function getCharset()
    {
        return $this->_charset;
    }
            
    /**
     * Возвращает название текущего языка
     * @return string;
     */
    public function getLang()
    {
        return $this->_lang;
    }
    
    public function getExt()
    {
        return $this->_ext;
    }
    
	public function addHscriptUrl($script)
	{
		if(false === array_search($script, self::$_headJSScripts)){
            self::$_headJSScripts[] = $script;
        }
	}	
	
	public function addHCssUrl($script)
	{
		if(false === array_search($script, self::$_headCss)){
            self::$_headCss[] = $script;
        }
	}
	
    public function addHScript($script)
    {		
		$script = $this->media('/js/'.$script);
		
        if(false === array_search($script, self::$_headJSScripts)){
            self::$_headJSScripts[] = $script;
        }
    }
    
    public function addHCss($script)
    {
		$script = $this->media('/css/'.$script);
        if(false === array_search($script, self::$_headCss)){
            self::$_headCss[] = $script;
        }
    }
	
	public function addGlHScript($script)
	{		
		if(0 === strpos($script, '/')){
			$script = substr($script, 1);
		}
						
		$script = $this->mediaGlob('/js/'.$script);
		
        if(false === array_search($script, self::$_headJSScripts)){
			
            self::$_headJSScripts[] = $script;			
        }
	}
	
	public function addGlHCss($script)
	{
		if(0 === strpos($script, '/')){
			$script = substr($script, 1);
		}
		
		$script = $this->mediaGlob('/css/'.$script);
        if(false === array_search($script, self::$_headCss)){
            self::$_headCss[] = $script;
        }
	}
    
    
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }    
    
    /**
     * Устанавливает тайтл страницы
     * @param string $title 
     */
    public function setTitle($title)
    {
        $this->_title = $title;
        return $this;
    }
    
    /**
     * Устанавливает окончание файлов шаблонов и темплейтов
     * @param string $ext
     * @return \Services\View
     */
    public function setExtension($ext)
    {
        $this->_ext = $ext;
        return $this;
    }
    
    /**
     * Устанавливает Шаблон страницы
     * @param string $name
     */
    public function setLayout($name)
    {
        $this->_layout = $name;
        return $this;
    }
    
    /**
     * Устанавливает кодировку страницы в шапке
     * @param string $charset
     */
    public function setCharset($charset)
    {
        $this->_charset = $charset;
        return $this;
    }
    
    /**
     * Устанавливает язык страницы в шаблоне.  <html lang="ru">
     * @param string $lang
     */    
    public function setLang($lang)
    {
        $this->_lang = $lang;
        return $this;
    }
    
    /**
     * Устанавливает путь к директории с шаблонами
     * @param string $path
     */
    public function setLayoutPath($path)
    {
        $this->layoutPath = $path;
        return $this;
    }
    
    public function setContentHTML($contentHTML)
    {
        $this->contentHTML = $contentHTML;
		return $this;
    }
    /**
     * Устанавливает путь к директории с темплейтами
     * @param string $path
     */
    public function addTemplatePath($path)
    {
        $this->templatePathes[] = $path;
        return $this;
    }
    
    /**
     * Возвращает путь к каталогу медиа
     * @param type $path Путь к медиа контенту внутри папки медиа
     * @return string Полный путь до файла
     */
    public function media($path)
    {
        return $this->mediaPath.$path;
    }        
	
	/**
     * Возвращает путь к каталогу медиа
     * @param type $path Путь к медиа контенту внутри папки медиа
     * @return string Полный путь до файла
     */
    public function mediaGlob($path)
    {
        return $this->globalMediaPath.$path;
    }
	
	public function showContentHTML()
	{
		echo $this->contentHTML;
	}
	
	public function showPage()
	{
		echo $this->page;
	}					    
      
	public static function setMediaPath($mediaPath)
	{
		self::$_mediaPath = $mediaPath;
	}
	
	public static function setGlobalMediaPath($globalMediaPath)
	{
		self::$_globalMediaPath = $globalMediaPath;
	}
}

<?php
namespace Kernel\Services\Viewer;

use Kernel\Interfaces\ViewInterface;
use Kernel\Services\FileDataStorage;

/**
 * Description of TwigViewer
 *
 * @author sworion
 */
class TwigViewer implements ViewInterface {
	
	const templateExtension = '.html.twig';
	/** @var Twig_Environment */
	private $twig;
	private $cachePath;
	
	public function __construct(string $cachePath='', string $layoutsPath='')
	{
		$this->cachePath = $this->createCacheDir($cachePath);
		
		\Twig_Autoloader::register();
		$loader = new \Twig_Loader_Filesystem();
		
		$this->twig = new \Twig_Environment($loader, [
			'cache'=>$this->cachePath
		]);
		
		if($layoutsPath){
			if(!file_exists($layoutsPath)){
				throw new \FileNotFoundException('Не найден путь до папки с layouts');
			}
			
			$this->addTemplatePath($layoutsPath, 'layouts');
		}
		var_dump($this);
	}
	
	public function getFileExtension(): string
	{
		return self::templateExtension;
	}
	
	public function getLang()
	{
		
	}
	
	public function getLayout()
	{
		
	}
	
	public function getCharset()
	{
		
	}
	
	public function setlayout($layout)
	{
		
	}
	
	public function setCharset($charset)
	{
		
	}
	
	public function setLang($lang)
	{
		
	}
	
	public function createCacheDir($path)
	{
		FileDataStorage::makeDir($path);
	}
	
	public function addTemplatePath($path, $namespace='')
	{
		$this->twig->getLoader()->addPath($path, $namespace);
	}
	
	public function render(string $template, array $params = [], string $namespace=''): string
	{				
		$template .= self::templateExtension;
		if($namespace){
			$template = "@$namespace/".$template;
		}
		
		return $this->twig->render($template, $params);
	}
}

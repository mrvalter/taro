<?php
namespace Kernel\Services\Viewer;

use Kernel\Interfaces\ViewInterface;
use Kernel\Services\FileDataStorage;
use Kernel\Services\Viewer\Extensions\WidgetTokenParser;

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
	
	public function __construct(string $cachePath='', string $layoutsPath='', array $extensions=[])
	{
		\Twig_Autoloader::register();
		
		$loader = new \Twig_Loader_Filesystem();
		if($layoutsPath){
			if(!file_exists($layoutsPath)){
				throw new \FileNotFoundException('Не найден путь до папки с layouts');
			}
			
			$loader->addPath($layoutsPath, 'layouts');
		}
		$this->cachePath = $this->createCacheDir($cachePath);
		
		$twig = new \Twig_Environment($loader, [
			'cache'=>$this->cachePath
		]);
		
		/* Добавляем обработчик Виджетов  */
		$twig->addTokenParser(new WidgetTokenParser($twig));
		
		/* Добавляем пути к медиа контенту */		
		$twig->addGlobal('MEDIA', (object)['global'=>'path_to_global', 'bundle'=>'path_to_bundle_media']);
		
		/* функция дампа переменной */		
		
		$twig->addFunction(new \Twig_SimpleFunction('dump', function ($array) {
			extension_loaded('xdebug') ? var_dump($array) : printf("<pre>%s</pre>", print_r($array, true));
		}));
		
		$twig->addFunction(new \Twig_SimpleFunction('widget', function (...$array) {
			var_dump($array);
		}));
		
		
		
		$this->twig = $twig;
	}
	
	public function createTemplate(string $stringTemplate): \Twig_Template
	{
		
		return $this->twig->createTemplate($stringTemplate);
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

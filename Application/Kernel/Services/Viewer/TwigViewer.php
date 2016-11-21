<?php
namespace Kernel\Services\Viewer;
use Kernel\Interfaces\ViewInterface;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_Autoloader;
/**
 * Description of TwigViewer
 *
 * @author sworion
 */
class TwigViewer {
	
	/** @var Twig_Loader_Filesystem */
	private $loader;
	
	public function __construct()
	{
		Twig_Autoloader::register();
		$this->loader = new Twig_Loader_Filesystem();		
	}
	
	public function addTemplatePath($path, $namespace='')
	{
		$this->loader->addPath($path, $namespace);
	}
	
	public function render(string $template, array $params = []): string
	{
		$twig = new Twig_Environment($this->loader);
		return $twig->render($template, $params);
	}
}

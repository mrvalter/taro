<?php
namespace Kernel\Services\Router;

use Kernel\Services\HttpFound\Response;
/**
 * Description of Route
 *
 * @author sworion
 */
class LocalRoute extends Route{
	
	private $bundle;
	private $controller;
	private $action;
	private $config;
	private $rights;
	private $serviceContainer;
	
	public function __construct($controller, array $params=[])
	{
		
		$this->controller = $controller;		
	}
	
	public function execute(): Response
	{
		
	}
	
	
}

<?php
namespace Kernel\Services\Router;

use Kernel\Interfaces\RouteInterface;
use Kernel\Services\HttpFound\Response;
use Kernel\Classes\Types\ObjectCollection;

abstract class Route implements RouteInterface {		
	
	private $subRoutes = null;
	
	public function __construct()
	{
		$this->subRoutes = new ObjectCollection();
	}
	
	public function addSubRoute(RouteInterface $route)
	{
		$this->subRoutes->push($route);
		return $this;
	}
		
	abstract function execute(): Response;
}

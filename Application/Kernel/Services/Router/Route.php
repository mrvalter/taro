<?php
namespace Kernel\Services\Router;

use Kernel\Interfaces\RouteInterface;
use Kernel\Services\HttpFound\Response;
use Kernel\Classes\Types\ObjectCollection;

abstract class Route implements RouteInterface {		
	
	private $subRoutes = null;
	
	/** @var Response */
	protected $response;
	
	public function __construct()
	{
		$this->subRoutes = new ObjectCollection();
		$this->response = new Response();
	}
	
	public function addSubRoute(RouteInterface $route)
	{
		$this->subRoutes->push($route);
		return $this;
	}		
	
	abstract public function execute(): Response;
}

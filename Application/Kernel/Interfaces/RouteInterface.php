<?php
namespace Kernel\Interfaces;

use \Psr\Http\Message\ResponseInterface;
/**
 *
 * @author sworion
 */
interface RouteInterface {
	
	public function execute(): ResponseInterface;
}

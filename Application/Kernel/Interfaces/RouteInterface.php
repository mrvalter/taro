<?php
namespace Kernel\Interfaces;

use Kernel\Services\HttpFound\Response;

/**
 *
 * @author sworion
 */
interface RouteInterface {
	
	public function execute(): Response;
}

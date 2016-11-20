<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kernel\Services\Router;

use Kernel\Services\HttpFound\Response;
/**
 * Description of ResponseRoute
 *
 * @author sworion
 */
class ResponseRoute extends Route{
	
	private $response = null;
	
	public function __construct(Response $response) 
	{
		
		$this->response = $response;
	}
	
	public function execute(): Response
	{
		
		return $this->response;
	}
}

<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kernel\Interfaces;

/**
 *
 * @author sworion
 */
interface ServiceContainerInterface {
		
	public function addService(string $name, $object);
	
}

<?php
namespace Services\Interfaces;

use Services\Interfaces\SecurityInterface;
use Psr\Http\Message\RequestInterface;

interface FirewallInterface {

	/** @return SecurityInterface */
	public function getSecurity();	
	
	/**
	 * @return string
	 */
	public function getBundlesPath();
	
	/**
	 * Возвращает название подключенного бандла по имени, false если не находит
	 * @param string $name
	 * @return string|false
	 */
	public function getBundleByName($name);
		
	/**
	 * 
	 * @param RequestInterface $request
	 * @return boolean
	 */
	public function checkAccess(RequestInterface $request);
	
	/**
	 * 
	 * @param array $config
	 */
	public function setConfig(array $config=[]);
		
}

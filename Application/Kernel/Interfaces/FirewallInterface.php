<?php
namespace Kernel\Interfaces;
use Kernel\Interfaces\SecurityInterface;
use Kernel\Services\HttpFound\Uri;

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
	public function checkAccess(Uri $uri);
	
	/**
	 * 
	 * @param array $config
	 */
	public function setConfig(array $config=[]);
		
}

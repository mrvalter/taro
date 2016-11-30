<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Kernel\Services;
use DB\DBConnect;

/**
 * @category MED CRM
 */
class DB {
	
	private static $dbs;
	
	private $config;
	
	public function __construct(array $connects=[])
	{		
		$this->config = $connects;		
	}
	
	/**
	 * 
	 * @param string $name
	 * @return DBConnect
	 * @throws \DBException
	 */
	public function getDBConn($name)
	{
		
		if(isset(self::$dbs[$name])){
			return self::$dbs[$name];
		}
		
		$config = $this->config;
		
		
		if(isset($config[$name])){
			
			$user     = isset($config[$name]['user'])     ? $config[$name]['user']     : '';
			$password = isset($config[$name]['password']) ? $config[$name]['password'] : '';
			$host     = isset($config[$name]['host'])     ? $config[$name]['host']     : '';
			$encoding = isset($config[$name]['encoding']) ? $config[$name]['encoding'] : '';
			$dbname   = isset($config[$name]['dbname'])   ? $config[$name]['dbname']   : '';
			$driver   = isset($config[$name]['driver'])   ? $config[$name]['driver']   : 'mysql';
			$port     = isset($config[$name]['port'])     ? $config[$name]['port']     : '';			
			
			$class = '\\Kernel\\Services\\DB\\'.ucfirst($driver).'Driver';			
			return self::$dbs[$name] = new $class($user, $password, $host, $encoding, $dbname);
		}
		
		throw new \DBException('Конфиг базы данных с индексом "'.$name.'" не найден');
	}
	
}

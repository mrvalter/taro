<?php
/** 
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 * @package Services\DB
 */
namespace Kernel\Services\DB;
use Kernel\Services\Logger as Logger;

/**
 *  Класс работы с библиотекой PDO 
 */
class PDODriver extends \PDO{                            
	
	/**
	 * 
	 * @param string $sql
	 * @return \PDOStatement
	 */
	public function prepare($sql, $options = null)
	{		        		
		return parent::prepare($sql);
	}
	
    public function exec($statement) {
		$start = microtime(true);
		$result = parent::exec($statement);
		Logger::pushDb($statement, microtime(true) - $start);		        
		return $result; 
    }
	public function query($string)
	{
		$start = microtime(true);
		$result = parent::query($string);
		Logger::pushDb($string, microtime(true) - $start);		        
		return $result; 
		
	}
    
}

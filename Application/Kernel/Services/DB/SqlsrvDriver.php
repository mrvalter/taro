<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Kernel\Services\DB;
/**
 * @category MED CRM
 */
class SqlsrvDriver extends DBConnect {
		

	protected function createPDO()
	{
		$options = [
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
		];
		  
		$pdo = new \PDO("sqlsrv:Server=".$this->_host.";Database=".$this->_dbname, $this->_user, $this->_password);		  	
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
		
		return $pdo;
	}
	
	protected function createLink()
	{
		$dbcnx = @mssql_connect($this->_host.':'.$this->_port,$this->_user,$this->_password);
		if (!$dbcnx) {
			throw new \DBException("Connect failed: %s\n", mssql_get_last_message());
		} 

		if (!mssql_select_db($this->_dbname, $dbcnx)) { 
			throw new \DBException("Select DB failed: %s\n", mssql_get_last_message());
		} 
				
        return $dbcnx;
	}	
}

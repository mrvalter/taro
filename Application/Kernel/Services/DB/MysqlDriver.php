<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Kernel\Services\DB;
/**
 * @category MED CRM
 */
class MysqlDriver extends DBConnect {								
	
	protected function createPDO()
	{
		$options = [
			\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $this->_encoding,
			\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION            
		];
		
		$str = 'mysql:host='.$this->_host
				.($this->_port? ';port='.$this->_port : '')
				.';dbname='.$this->_dbname;			
        $pdo = new PDODriver($str, $this->_user, $this->_password, $options);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $pdo->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('Kernel\Services\DB\Statement', array($pdo)));
		return $pdo;
	}
	
	protected function createLink()
	{
		return null;
	}
}

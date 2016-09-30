<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services\DB;
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
        $pdo->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('Services\DB\Statement', array($pdo)));
		return $pdo;
	}
	
	protected function createLink()
	{
		$link = mysqli_connect($this->_host, $this->_user,$this->_password, $this->_dbname, $this->_port);
        if (mysqli_connect_errno()) { 
            throw new \DBException("Connect failed: %s\n", mysqli_connect_error());
        }
        mysqli_query($link,'SET NAMES "'.$this->_encoding.'"');
        return $link;
	}		
}

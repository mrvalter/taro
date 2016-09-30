<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Services\DB;

use Services\Logger as Logger;
/**
 * Description of Statement
 *
 * @author sworion
 */
class Statement extends \PDOStatement{
    
    protected function __construct(\PDO $connection)
	{
		$this->connection = $connection;
	}
    
    public function execute($bound_input_params = NULL) 
    {        
        $start = microtime(true);
        $result = parent::execute($bound_input_params);        
		Logger::pushDb($this->queryString, microtime(true) - $start);
		return $result;        
    }
}

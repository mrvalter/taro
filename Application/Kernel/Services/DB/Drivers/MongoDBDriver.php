<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Kernel\Services\DB\Drivers;
/**
 * @category MED CRM
 */
class MongoDBDriver extends DBConnect {								
	
	protected function createPDO()
	{
		return null;
	}		
	
	protected function createLink() 
	{
				
		return new \MongoDB\Driver\Manager("mongodb://{$this->_user}:{$this->_password}@{$this->_host}".
				($this->_port ? ":{$this->_port}":"")."/{$this->_dbname}");
	}
}

class MongoDB {
	
	private $manager;
	private $db;
	
	public function __construct(\MongoDB\Driver\Manager $manager) {
		parent::__construct($connectStr);
	}
	
	
}
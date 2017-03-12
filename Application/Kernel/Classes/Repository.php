<?php
namespace Kernel\Classes;

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
use Services\QueryBuilder as QueryBuilder;
use Services\FormLoader as FormLoader;

/**
 * @category MED CRM
 */
abstract class Repository {

    private static $serviceContainer = null;
    
    protected $errors = [];    	
    protected $user;	              
	
	
	public function __construct() {
		if(null === self::$serviceContainer){
			throw new \SystemErrorException('Service Container not load in Repository class');
		}
	}
	
	public function getServiceContainer()
	{
		return self::$serviceContainer;
	}
    /**
    * 
    * @param type $name
    * @return \Services\DB\DbConn
    */
    public function getDbConn($name){
        return $this->getServiceContainer()->get('database')->getDbConn($name);
    }			    
	
	
    /**
     * 
     * @param type $name
     * @return \Services\DB\PDODriver
     */
    public function getPDOFrom($name)
    {
        return $this->getDbConn($name)->getPDO();
    }

	public function getConnFrom($name)
	{
		return $this->getDbConn($name)->getLink();
	}
	
	
    public function _makeObjects(PDOStatement $stmt, $entityName)
    {
        return $stmt->fetchAll(PDO::FETCH_CLASS, $entityName);
    }
	
	public function getUser()
	{
		
	}
	/**
	 * @deprecated
	 * @return \Services\DB
	 */
	public function getDb()
	{
		return $this->getDbConn('db');
	}
	
	/**
	 * @deprecated
	 * @return \Services\DB
	 */
	public function getDbOffice()
	{
		return $this->getDbConn('dbOffice');
	}
	
	
	public function setMainTable($tableName)
	{
		$this->mainTable = $tableName;
	}
	
	
	public function getById($id)
	{
		
		if(!$id || !is_numeric($id)){
			return null;
		}				
		
		$sql = 'SELECT * FROM `'.static::tableName.'` WHERE id = :id';
		$stmt = $this->getPDOFrom('db')->prepare($sql);
		$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchObject(static::entityName);
				
	}
	
	public function getAll($params=array(), $perPage=50)
	{		
		$page = isset($params['page'])? $params['page'] : 1;
		$sql = 'SELECT * FROM `'.static::tableName.'` ORDER BY `name`';
		$builder = new QueryBuilder($this->getPDOFrom('db'));
		$rows = $builder->setQuery($sql)
				->withPager($page, false)
				->fetchClass(static::entityName);
		
		return $rows;
	}
	
	public function delByIds($ids)
	{

		if(!is_array($ids)){
			$ids = [$ids];
		}
		foreach($ids as $i=>$id){
			if(!is_numeric($id)){
				unset($ids[$i]);
			}
		}
		if(!sizeof($ids)){
			return;
		}
		
		$sql = 'DELETE FROM '.static::tableName.' WHERE id IN ('.implode(',', $ids).')';		
		$stmt = $this->getPDOFrom('db')->prepare($sql);
		$stmt->execute();
		return true;
	}
	
	/**
	 * 
	 * @param FormLoader $formLoader
	 * @return entity
	 * @throws RepositoryException
	 */
	public function update(FormLoader $formLoader)
	{
		$updateValues = $formLoader->getChangeProps();
		$entity = $formLoader->getEntity();
	
		/* Если поля для не переданы то и сохранять нечего */
		if(!sizeof($updateValues))
			return $entity;
		
		$entityName = static::entityName;
		$tableName = static::tableName;
		if(!$entity instanceof $entityName){
			throw new RepositoryException('Невозможно сохранить запись, переданный объект не '.static::entityName);
		}
								
		$where = '';
		if($entity->id){
			$sql = 'UPDATE `'.$tableName.'`';
			$where = 'WHERE id=:id';
		}else{
			$sql = 'INSERT INTO `'.$tableName.'`';
		}
		
		foreach($updateValues as $name){
			$sets[] = "`$name` = :$name ";
		}
		
		$sql .= ' SET '.implode(', ', $sets).' '.$where;
		
		$pdo = $this->getPDOFrom('db');		
		$stmt = $pdo->prepare($sql);
		
		foreach($updateValues as $name){
			$stmt->bindValue(":$name", $entity->$name);
		}
		
		if($entity->id){
			$id = $entity->id;
			$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		}
		
		$stmt->execute();	
		
		if($entity->id){
			return $entity;
		}
		
		$id = $pdo->lastInsertId();	
		$entity = $this->getById($id);
		
		return $entity;
		
	}
	
	public function getList()
	{		
		$tableName = static::tableName;
				
		$sql = 'SELECT id, name FROM `'.$tableName.'`';
				
		$stmt = $this->getPDOFrom('db')->prepare($sql);
		$stmt->execute();
		
		$return = [];
		while ($row = $stmt->fetch(PDO::FETCH_LAZY))
		{
			$one['id'] = $row['id'];
			$one['name'] = $row['name'];
			$return[] = (object)$one;
			
		}
		
		return $return;
		
	}
	
	public function getErrors()
	{
		return $this->errors;
	}
	
	public function getConfig($name='')
	{
		
		return $name ? $this->getApp()->getService('_config')->get($name) : $this->getApp()->getService('_config');
	}
	
	/**
	 * Удаляет нецифровые значяения массива или значения меньше единицы
	 * @param array $array
	 * @return array
	 */
	public function checkNumericArray(&$array)
	{
		if(!is_array($array)){		
			$array = [$array];
		}
		$ids = [];
		foreach ($array as $value){
			if(is_numeric($value) && $value>0){
				$ids[] = $value;				
			}
		}
		$array = $ids;
		return $ids;
	}
	
	public function getPathToSelfBundle()
	{	
		return $this->getApp()->getPathToSelfBundle();
	}
        
        public static function setServiceContainer($serviceContainer)
        {
            self::$serviceContainer = $serviceContainer;
        }
}

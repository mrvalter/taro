<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services;

use Services\QueryBuilder\Pager as Pager;
use Services\DB\DBConnect as DBConnect;
/**
 * @category MED CRM
 */
class QueryBuilder {
	
	private $pdo    = null;
	private $pager = null;
	private $query;
    private $bindParams;
	private $countQuery;
	
	public function __construct(\PDO $pdo)
	{		
		$this->pdo = $pdo;
	}
	
	public function setQuery($sql, $bindParams=[], $countQuery = '')
	{
		$this->query = $sql;
        $this->bindParams = $bindParams;
		$this->countQuery = $countQuery;
		return $this;
	}
	
	public function withPager($page=1, $perPage=50)
	{
		$this->pager = new Pager($page, $perPage);
		return $this;
	}
	
	public function execute()
	{
		if(null !== $this->pager){
			$countSQL = $this->countQuery ? $this->countQuery :
					'SELECT COUNT(*) FROM ('.$this->query.') as COUNT_TABLE';

			$stmt = $this->pdo->prepare($countSQL);
			if(sizeof($this->bindParams)){
				foreach($this->bindParams as $name=>$params){
					$stmt->bindValue($name, $params[0], (isset($params[1])? $params[1] : \PDO::PARAM_STR));
				}
			}

			$stmt->execute();
			$this->pager->setTotalItems($stmt->fetch()['COUNT(*)']);
		}
		
		
		$query = $this->buildQuery();
        $stmt = $this->pdo->prepare($query);
        if(sizeof($this->bindParams)){
            foreach($this->bindParams as $name=>$params){
                 $stmt->bindValue($name, $params[0], (isset($params[1])? $params[1] : \PDO::PARAM_STR));
            }
        }
        $stmt->execute();
		return $stmt;						
	}
	
	public function fetchClass($class)
	{
		$stmt = $this->execute();        
		$rows = $stmt->fetchAll(\PDO::FETCH_CLASS, $class);
		$stmt->closeCursor();
		return $rows;
	}
	
	public function getStmt()
	{
		return $this->execute();
	}
	
	public function getPager()
	{
		return $this->pager;
	}
	private function buildQuery()
	{
		if(null !== $this->pager){
			
			if($this->pager->countPerPage != 0){
				$limit = 'LIMIT '.($this->pager->page - 1) * $this->pager->countPerPage.', '.$this->pager->countPerPage;
            }else {
				$limit = '';
            }
			$this->query = preg_replace('~\slimit.*~is', '', $this->query);							
			$this->query .= ' '.$limit;
		}
		
		return $this->query;
	}
}

<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services\QueryBuilder;

/**
 * @category MED CRM
 */
class Pager {
	
	use \Getter;
	
	private $page;
	private $countPerPage;
	private $totalItems;
	private $countPages;
	
	public function __construct($page, $countPerPage)
	{
		$this->page = $page;
		$this->countPerPage = $countPerPage;
	}	
	
	public function setTotalItems($count)
	{
		$this->totalItems = $count;
		$this->countPages = $this->totalItems%$this->countPerPage ? (int)($this->totalItems/$this->countPerPage)+1 : $this->totalItems/$this->countPerPage;
	}		
}

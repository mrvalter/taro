<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services\ListsBuilder;
/**
 * @category MED CRM
 */
class Row {
	
	private $id;
	private $cellsCollection;
	
	public function __construct(){
		$this->cellsCollection = new CellsCollection();
	}
	
	public function addCell(Cell $cell)
	{
		$this->cellsCollection->push($cell);
	}
}

<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services\ListsBuilder;

/**
 * @category MED CRM
 */
class CellsCollection implements \Iterator{
	
	private $cells;
	
	public function rewind()
    {
        reset($this->cells);
		return $this;
    }
  
	/**
	 * 
	 * @return MenuItem
	 */
    public function current()
    {
        $var = current($this->cells);        
        return $var;
    }
  
    public function key() 
    {
        $var = key($this->cells);        
        return $var;
    }
  
	/**
	 * 
	 * @return MenuItem
	 */
    public function next() 
    {
        $var = next($this->cells);        
        return $var;
    }
  
    public function valid()
    {
		
        $key = key($this->cells);
        $var = ($key !== NULL && $key !== FALSE);        
        return $var;
    }
	
	public function push(Cell $cell)
	{
		
		$this->cells[] = $cell;		
		return $this;
	}
}

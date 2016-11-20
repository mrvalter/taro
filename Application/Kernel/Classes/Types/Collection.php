<?php
namespace Kernel\Classes\Types;

class Collection implements \Iterator{		
	
	protected $rows=[];
	
	public function __construct(array $objects)
	{		
		$this->rows = $selected;
	}								
	
	public function push($row)
	{
		$this->rows[] = $row;
	}
	
	public function rewind()
    {
        reset($this->rows);
		return $this;
    }
  
	
    public function current()
    {
        $var = current($this->rows);        
        return $var;
    }
  
    public function key() 
    {
        $var = key($this->rows);        
        return $var;
    }
  	
    public function next() 
    {
        $var = next($this->rows);        
        return $var;
    }
  
    public function valid()
    {		
        $key = key($this->rows);
        $var = ($key !== NULL && $key !== FALSE);        
        return $var;
    }
	
	public function count()
	{		
		return count($this->rows);
	}
}
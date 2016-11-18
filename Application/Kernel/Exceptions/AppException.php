<?php

class AppException extends Exception{
	
    protected $sysmessage;
    
    public function __construct(string $message = "",  string $sysmessage='', int $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->sysmessage = $sysmessage;
    }
    
    public function showTable(){
            echo '<table>';
            echo $this->xdebug_message;
            echo '</table>';
    }
    
    /**
     * 
     * @return string
     */
    public function getSysMessage()
    {
        
        return $this->sysmessage;
    }
	
}

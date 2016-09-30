<?php

class AppException extends Exception{
	
	public function showTable(){
                echo '<table>';
                echo $this->xdebug_message;
                echo '</table>';
	}
	
}

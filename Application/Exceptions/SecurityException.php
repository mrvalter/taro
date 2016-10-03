<?php

class SecurityException extends AppException{
	
	public function showTable(){
                echo '<table>';
                echo $this->xdebug_message;
                echo '</table>';
	}
	
}

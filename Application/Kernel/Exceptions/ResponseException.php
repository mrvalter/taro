<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SystemResponceException
 *
 * @author sworion
 */
class ResponseException extends AppException {
	
	public function __construct(int $code=0, string $message = "", string $sysmessage = '', \Throwable $previous = null) {
		parent::__construct($message, $sysmessage, $code, $previous);
	}
}

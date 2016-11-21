<?php

class NonAuthorisedException extends ResponseException {
	
	public function __construct(string $message = "", string $sysmessage = '', \Throwable $previous = null) {
		parent::__construct(401, $message, $sysmessage, $previous);
	}
}
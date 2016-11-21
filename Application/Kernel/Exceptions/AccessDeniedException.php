<?php

class AccessDeniedException extends ResponseException {
	
	public function __construct(string $message = "", string $sysmessage = '', \Throwable $previous = null) {
		parent::__construct(403, $message, $sysmessage, $previous);
	}
}
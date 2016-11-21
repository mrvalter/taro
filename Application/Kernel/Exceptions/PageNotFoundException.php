<?php

class PageNotFoundException extends ResponseException {
	
	public function __construct(string $message = "", string $sysmessage = '', \Throwable $previous = null) {
		parent::__construct(404, $message, $sysmessage, $previous);
	}
}

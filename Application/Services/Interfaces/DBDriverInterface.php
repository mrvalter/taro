<?php
namespace Services\Interfaces;

interface DBDriverInterface {
    
    public function __construct($host, $user, $password, $dbname, $encoding);
    
}

<?php
namespace Kernel\Interfaces;

interface DBDriverInterface {
    
    public function __construct($host, $user, $password, $dbname, $encoding);
    
}

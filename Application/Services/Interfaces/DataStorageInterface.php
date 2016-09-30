<?php
namespace Services\Interfaces;

interface DataStorageInterface {
    
    public function get($key);
    public function set($key, $value);
    
    public function checkUp($key);
        
}

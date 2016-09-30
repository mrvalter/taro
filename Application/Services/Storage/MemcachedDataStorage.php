<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */

namespace Services\Storage;

use Services\Interfaces\DataStorageInterface as DataStorageInterface;

/**
 * @category MED CRM
 */
class MemcachedDataStorage implements DataStorageInterface{
    
    private $storage = null;    
    
    public function __construct($server, $port)
    {        
        $memcacheD = new \Memcached();
        $memcacheD->addServer($server, $port);
        $this->storage = $memcacheD;
    }
            
    public function get($key)
    {
        return $this->storage->get($key);
    }
    
    public function set($key, $value, $time=3600)
    {
        $this->storage->set($key, $value, time()+$time);
        return $this;
    }
    
    public function checkUp($key)
    {
        $this->get($key);      
        if ($this->storage->getResultCode() == \Memcached::RES_NOTFOUND) {
            return false;
        }
        
        return true;
    }
    
}

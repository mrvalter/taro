<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services\Storage;
use Services\Interfaces\DataStorageInterface as DataStorageInterface;
use Services\Config as Config;

/**
 * @category MED CRM
 */
class FileDataStorage implements DataStorageInterface{
    
    const CACHE_DIR = 'cache';
    
    private $cachePath;
    
    
    public function __construct(Config $config) 
    {
        $this->cachePath = $config->get('APP_PATH').'/'.self::CACHE_DIR;
        
    }
    
    public function get($name)
    {
        
    }
    
    public function set($name, $value) 
    {
        ;
    }
    
    public function checkUp() 
    {
        return file_exists($this->cachePath);
    }
    
}

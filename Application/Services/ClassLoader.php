<?php
namespace Services;

class ClassLoader {
    
    
    private $_appDir;
    private $map=[];
	private $namespaces;
    
    public function setApplicationDir($dir)
    {
        if(!file_exists($dir)){            
            throw new \Exception("classLoaderDir '$dir' not found");
        }
        $this->_appDir = $dir;
    }
    /**
     * Registers this instance as an autoloader.
     *
     * @param bool $prepend Whether to prepend the autoloader or not
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }

    /**
     * Unregisters this instance as an autoloader.
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * Loads the given class or interface.
     *
     * @param  string    $class The name of the class
     * @return bool|null True if loaded, null otherwise
     */
    public function loadClass($class)
    {
        if ($file = $this->findFile($class)) {
            require_once $file;
            return true;
        }
    }
    
    public function addMapItem($mapItem)
    {
        if(!in_array($mapItem, $this->map)){		
            $this->map[] = $mapItem;
        }
		
            
    }
	
	public function addMapItemUnshift($mapItem)
	{
		if(!in_array($mapItem, $this->map)){		
            array_unshift($this->map, $mapItem);
        }
	}
	
	public function addNamespace($namespace, $path)
	{
		$this->namespaces[$namespace][] = $path;
	}
	
    public function findFile($class)
    {       
        // work around for PHP 5.3.0 - 5.3.2 https://bugs.php.net/50731
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }
        
		$pathDir = $this->_appDir;
		
		
		/* Проверяем заданные неймспейсы */
		if(sizeof($this->namespaces)){
			foreach($this->namespaces as $namespace=>$pathes){
				
				if(0 === strpos($class, $namespace.'\\')){
					foreach($pathes as $path){

							$class = substr($class, strlen($namespace)+1);
							$pathDir = $path;
							break; break;					
					}
				}
			}
		}				
		
        $pathArr = explode('\\', $class);

        $className = array_pop($pathArr);				
		
        $file = $pathDir.'/'.( sizeof($pathArr)? implode('/',$pathArr).'/' : '').$className.'.php';
		
        if(file_exists($file)){
            return $file;               
        }
        
        if(isset($this->map[0])){                    
            foreach($this->map as $map){
				$file = $map.'/'.( sizeof($pathArr)? implode('/',$pathArr).'/' : '').$className.'.php';
				if($class=='EventssRepository'){
					var_dump($file.' ppp');
				}
                if(file_exists($file)){
                    return $file;                
                }
            }
            
        }
        return false;                        
    }
	
	public function includeFile($file)
	{
		if(!file_exists($file)){
            throw new \FileNotFoundException("Не найден файл '$file'");
        }
		require_once $file;
		
		return true;
	}
    
}

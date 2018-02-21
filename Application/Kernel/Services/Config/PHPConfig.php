<?php
namespace Kernel\Services\Config;

use Kernel\Interfaces\ConfigInterface;
/**
 * Description of PHPConfig
 *
 * @author sworion
 */
class PHPConfig extends Config implements ConfigInterface {
	
	 /**
     * Добавляет конфиг по имени файла или имени папки с конфигами
     * @param string $file Путь до файла
     * @param string $key Ключ в конфигурационном массиве
     * @param boolean $required Вызывыть исключение, если файла нет. По умолчанию TRUE
     * @throws \ConfigException
     * @return \Services\Config
     */		
    public function addFile(string $file, bool $required = true, string $key=''): ConfigInterface
    {	
        
        $fileExist = file_exists($file);                  

        if(!$fileExist && $required){
            throw new \ConfigException('Не найден файл конфига '. $file);
        }                
        		
        if($key && !isset($this->config[$key])){
            $this->config[$key] = [];
        }                        
        
        if(!$fileExist){
			return $this;
		}
				
		try {
			$config = include $file;
		} catch (\Exception $ex) {
			throw new \ConfigException( $ex->getMessage() );
		}
		
		if($key){
			$exConfig = &$this->config[$key];
		}else{
			$exConfig = &$this->config;
		}                                    

		$exConfig = array_replace_recursive($exConfig, $config);
        
        return $this;        
    }	
	
}

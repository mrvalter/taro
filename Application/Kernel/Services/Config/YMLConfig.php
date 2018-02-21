<?php
/**
 * @autor Fedyakin Alexander
 */

namespace Kernel\Services\Config;

use Symfony\Component\Yaml\Yaml;

/**
 * Класс конфигурации приложения
 */
class YMLConfig extends Config {

    /**
     * Добавляет конфиг по имени файла или имени папки с конфигами
     * @param string $file Путь до файла
     * @param string $key Ключ в конфигурационном массиве
     * @param boolean $required Вызывыть исключение, если файла нет. По умолчанию TRUE
     * @throws \ConfigException
     * @return \Services\Config
     */		
    public function addFile($file, bool $required = true, string $key = ''): self
    {	
        
        $fileExist = file_exists($file);                  

        if(!$fileExist && $required){
            throw new \ConfigException('Не найден файл конфига '.$fileName, $file);
        }                
        		
        if($key && !isset($this->config[$key])){
            $this->config[$key] = [];
        }                        
        
        if(!$fileExist){
			return $this;
		}
				
		try {
			$content = file_get_contents($file);
			$config = Yaml::parse($this->replaceTags($content));
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

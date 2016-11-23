<?php
/**
 * @autor Fedyakin Alexander
 */

namespace Kernel\Services;

use Symfony\Component\Yaml\Yaml;

/**
 * Класс конфигурации приложения
 */
class Config {                       
        
    const CONFIG_EXTENSION = 'php';
    
    /** @var array*/
    private $config;                
    
    /** @var array */
    private $tags;    	

    /** @var string */
    private $enviroment;            

    /**
     * 
     * @param string $enviroment  Переменная окружения (prod | dev | ...)
     * @param array $config       Массив конфигурации
	 * @param array $tags         Теги для замены в конфигурационном файле
     */
    public function __construct(array $config=[], string $enviroment = '', array $tags = [])
    {       
		
		$this->tags = $tags;
        $this->enviroment = $enviroment;
		$this->config = $config;		
    }
    
    /**
     * Возвращает объект конфига построенный с нужным ключом
     * @param string $conf Ключ массива конфигурации
     * @return self
     */			
    public function get(string $conf=''): self
    { 	
		
        if(!$conf){
            return $this;
        }
        
		return isset($this->config[$conf])? new Config($this->config[$conf]) : new Config();		
    }
    
    /**
     * Возвращает значение по ключу или null если зачения нет
     * @param ... ключи конфига
     * @return mixed | null
     */
    public function getValue(...$keys)
    {
        
        if(isset($keys[0])){
            $return = $this->config;
            foreach($keys as $key){
                if(isset($return[$key])){
                    $return = $return[$key];
                }else{
                    return null;
                }
            }            
            return $return;
        }
        
        return $this->config;
    }
    
    
    /**
     * Возвращает  теги для замены
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }
    
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
	
	/**
	 * Производит замену тегов и возвращает отформатированный текст
	 * @param string $content
	 * @return string 
	 */
	private function replaceTags(string &$content): string
	{		
		
		if(!sizeof($this->tags)){
			return $content;
		}
		
		$keys   = array_keys($this->tags);
		$values = array_values($this->tags);				
		return $content = str_replace($keys, $values, $content);
	}
}

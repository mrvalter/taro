<?php
/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Kernel\Services;

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
     * @param string $enviroment           Переменная окружения (prod | dev | ...)
     * @param string $pathToMainConfigDir  Папка с основным конфигом
     */
    public function __construct($enviroment='', array $array=[])
    {        
        $this->enviroment = $enviroment;
        $this->tags = [
            '<?php' => '',
            '<?'    => '',            
            '?>'    => '',
        ];
        
        $this->config = $array;
    }
    
    /**
     * Возвращает объект конфига построенным снужным ключом (клон)
     * @param string $conf Ключ массива конфигурации
     * @return Config
     */			
    public function get(string $conf='')
    { 	
        if($conf){
            $configArr = isset($this->config[$conf])? [$conf => $this->config[$conf]] : [];
        }else{
            return clone $this;
        }
        
        return new Config ($this->enviroment, $configArr);
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
     *  Возвращает  теги для замены
     * @return array
     */
    public function getTags()
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
	
    public function addFile($file, $key='', bool $required=true)
    {	
        
        $fileExist = file_exists($file);
        $config = [];            

        if(!$fileExist && $required){
            throw new \ConfigException('Не найден файл конфига '.$fileName, $file);
        }                
        
        if($key && !isset($this->config[$key])){
            $this->config[$key] = [];
        }                        
        
        if($fileExist){
            if($key){
                $exConfig = &$this->config[$key];
            }else{
                $exConfig = &$this->config;
            }
            
            $configStr = file_get_contents($file);            
            $config = eval($this->replaceTags($configStr));            
                          
            $exConfig = array_replace_recursive(
                $exConfig,
                $config
            );            
        }
        
        return $this;
        
    }                     				    		
    
    
    public function addDir($path, array $requiredNames=[])
    {
        if(!file_exists($path)){
            throw new \ConfigException('config\'s dir path not found', 'dir path: '.$path);
        }
        
        $files = [
            'config',
            'config_'.$this->enviroment
        ];
        
        foreach($files as $fileName){
            $file = $path.'/'.$fileName.'.'.self::CONFIG_EXTENSION;
            $this->addFile($file, '', in_array($fileName, $requiredNames));
        }
        
        return $this;
        
    }
    
    /**
     * 
     * @param array $tags
     * @return \Services\Config
     */
    public function addTags(array $tags=[])
    {
            $this->tags = array_merge($this->tags, $tags);
            return $this;
    }	   

    /**
     * 
     * @param type $config
     * @return \Services\Config
     */
    private function replaceTags(&$configStr)
    {		            		
        if(!sizeof($this->tags)){
            return $configStr;
        }
        
        $keys = array_keys($this->tags);        
        $values = array_values($this->tags);

        return str_replace($keys, $values, $configStr);                
    }
	
}

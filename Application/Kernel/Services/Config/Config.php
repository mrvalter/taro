<?php
/**
 * @autor Fedyakin Alexander
 */
namespace Kernel\Services\Config;

use Kernel\Interfaces\ConfigInterface;


/**
 * Класс конфигурации приложения
 */
abstract class Config {            
    
	const extensions = ['php', 'yml'];
	
	/** 
	 * Включать ли кеш конфига, для PHP расширения конфига всегда без кеша 
	 * Для DEV всегда без кеша
	 * @var string 
	 */	
	
    /** @var array*/
    protected $config;                
    
    /** @var array */
    protected $tags;    	

    /** @var string */
    protected $enviroment;          

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
		array_walk_recursive($config, [$this,'replaceTagsInArray']);
		$this->config = $config;
		
    }
    
	public function getEnviroment(): string
	{
		
		return $this->enviroment;
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
		
		$value = [];
        if(isset($this->config[$conf])){
			$value = is_array($this->config[$conf]) ? $this->config[$conf] : [$this->config[$conf]];
		}
		
		$class = get_class($this);
		
		return new $class($value);
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
    abstract public function addFile(string $file, bool $required, string $key): ConfigInterface;
	
	/**
	 * Производит замену тегов и возвращает отформатированный текст
	 * @param string $content
	 * @return string 
	 */
	protected function replaceTags(string &$content): string
	{		
		
		if(!sizeof($this->tags)){
			return $content;
		}
		
		$keys   = array_keys($this->tags);
		$values = array_values($this->tags);				
		return $content = str_replace($keys, $values, $content);
	}
	
	protected function replaceTagsInArray(&$item, $key)
	{
		if(empty($this->tags)){
			return;
		}
		
		$keys   = array_keys($this->tags);
		$values = array_values($this->tags);				
		$item = str_replace($keys, $values, $item);
	}
	
	
	static function makeConfigFromArray(array $confArr, string $extension, string $enviroment, $tags): ConfigInterface
	{		
		$class = self::getClassNameByExtension($extension);
		return new $class($confArr, $enviroment, $tags);
		
	}
	
	static function makeConfigFromDir(string $extension, string $enviroment, $configDirPath, array $tags=[]): ConfigInterface
	{
		$extension = strtolower($extension);		
		$class = self::getClassNameByExtension($extension);
		
		$config = new $class([], $enviroment, $tags);		
		$config->addFile($configDirPath.'/config.'.$extension, true);
		if($enviroment){
			$config->addFile($configDirPath."/config_{$enviroment}.".$extension, false);
		}
		$config->addFile($configDirPath.'/firewall.'.$extension, true, 'firewall');			
		return $config;
	}
	
	private static function getClassNameByExtension($extension)
	{
		$extension = strtolower($extension);
		$namespace = '\\Kernel\\Services\\Config\\';
		switch($extension){
			
			case 'php':
				return $namespace.'PHPConfig';
				
			case 'yml':				
				return $namespace.'YMLConfig';				
			
			default:
				throw new \ConfigException('Неверное расширение файлов конфига - "'. htmlspecialchars($extension).
						'". Возможные: '. implode(', ', self::extensions));
		}
	}
}

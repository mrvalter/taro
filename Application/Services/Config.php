<?php
/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services;

/**
 * Класс конфигурации приложения
 */
class Config {                       
    
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
    public function __construct($pathToMainConfigDir, $enviroment='')
    {
        
        $this->enviroment = $enviroment;   
        $this->config = array_replace_recursive(
            $this->getArrayFromFile($pathToMainConfigDir.'/config.php', true),
            $this->getArrayFromFile($pathToMainConfigDir.'/config_'.$this->enviroment.'.php', false)
        );                        
    }
    
    /**
     * Возвращает конфигурацию по ключу
     * @param string $conf Ключ массива конфигурации
     * @return mixed 
     */			
    public function get($conf='')
    { 		
        if($conf){
            return isset($this->config[$conf])?$this->config[$conf] : array();
        }else{
            return $this->config;
        }
    }
    
    /**
     * 
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * 
     * @param string $file
     * @param boolean $required
     * @return array
     * @throws \FileNotFoundException
     */	
    public function getArrayFromFile($file, $required=true)
    {
            if(file_exists($file)){
                    $array = include $file;
                    if(!is_array($array)){
                            $array = [];
                    }
            }elseif($required){
                    throw new \FileNotFoundException('Не найден файл конфигурации '.$file);
            }else{
                    $array = [];
            }

            return $array;
    }	
	
    /**
     * Добавляет конфиг по имени файла или имени папки с конфигами
     * @param string $file Путь до файла
     * @param string $key Ключ в конфигурационном массиве
     * @param boolean $required Вызывыть исключение, если файла нет. По умолчанию TRUE
     * @throws \ConfigException
     * @return \Services\Config
     */
	
    public function addFile($file, $required=true)
    {	
        
        $this->configFiles[] = ['path'=>$file, 'required'=>$required];
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
     * @return \Services\Config
     * @throws \ConfigException
     * @throws \FileNotFoundException
     */
    public function make()
    {
        
        $config = [];
        if(isset($this->configFiles[0])){                   				
            foreach($this->configFiles as $file){

                $path = $file['path'];
                $splFile = new \SplFileInfo($path);

                if(!$splFile->isReadable()){
                    if($file['required']){
                        throw new \FileNotFoundException('Не найден путь до конфига '.$path);
                    }
                    continue;
                }


                if($splFile->isDir()){
                    $config = array_replace_recursive(
                        $config,
                        $this->getArrayFromFile($path.'/config_prod.php', $file['required']),
                        $this->getArrayFromFile($path.'/config_'.$this->enviroment.'.php', false)
                    );                                                

                }else{
                    $config = array_replace_recursive(
                    $config,
                    $this->getArrayFromFile($path, $file['required'])
                    );
                }
            }

            /* Секюрный конфиг переписывает все предыдущее с одинаковыми ключами */
            foreach($this->configFiles as $file){

                $path = $file['path'];
                $splFile = new \SplFileInfo($path);           

                if($splFile->isDir()){
                    $securityArr = $this->getArrayFromFile($path.'/security.php', $file['required']);                
                    if(!empty($securityArr)){
                        $config['services'] = array_merge($config['services'], $securityArr['services']);
                    }                
                }
            }
        
        }
        
        $this->config = $this->replaceTags($config);
        $this->config['_tags'] = $this->getTags();

        return $this;
    }		

    /**
     * 
     * @param type $config
     * @return \Services\Config
     */
    private function replaceTags(&$config)
    {		            		
        if(!sizeof($this->tags)){
                return $this;
        }


        foreach($config as &$value){
            if(is_array($value)){
                $this->replaceTags($value);
            }else{				
                $sinonims = array_keys($this->tags);				
                $replaces = array_values($this->tags);
                $value = str_replace($sinonims, $replaces, $value);
            }
        }

        return $config;
    }
	
}

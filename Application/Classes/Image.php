<?php
/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 * @category MED CRM
 */
namespace Classes;
use \Services\FileDataStorage as FileDataStorage;

/**
 * Класс работы с изображениями.
 * 
 */
class Image {
       
	use Getter;
	
    const MAXW = 1000;
	const MAXH = 1000;
	const MAXS = 1000000; // максимальный размер
	
	const DIR_PATH_NONE = 0;
	const DIR_PATH_WORDS = 1;
	
	private static $_fileUplDir = null;
	private static $_cacheDir = null;
	private static $_mediaPath = null;
	
	private $fileUplDir = '/upload_files';
	private $fileCacheDir = '/cache/images';
	private $docRoot;	
	
	private $fullFilePath;	
	private $name;
    private $exists    = false;
    private $imageType = null;	
	private $mimeType = null;
	private $webPath   = '';
	
	private $_dirPathType;
	private $errors;
              
	/**
	 * 
	 * @param string $path относительно upload_files
	 */
    public function __construct($path='')
    {				
		if(self::$_fileUplDir !== null){
			$this->fileUplDir = self::$_fileUplDir;
		}
		
		if(self::$_cacheDir !== null){
			$this->fileCacheDir = self::$_cacheDir.'/images';
		}
				
		$this->_dirPathType = self::DIR_PATH_WORDS;								
		$this->docRoot = FileDataStorage::normalizePath($_SERVER['DOCUMENT_ROOT']);						
		$this->name = basename($path);
		
		if($path && $this->name){
			if(substr($path, 0, 1) == '/'){
				$this->fullFilePath = $path;
				$this->webPath = strpos($path, $this->docRoot) === 0 ? str_replace($this->docRoot, '', $path) : '';
			}else{
				$this->fullFilePath = $this->docRoot.$this->fileUplDir.$path;
				$this->webPath = $this->fileUplDir.$path;
			}
		}
		
					
		if(file_exists($this->fullFilePath)){
				$this->exists = true;															
		}
				
		if(!$this->exists){
			$this->name = 'no_image.png';			
			$this->webPath = self::$_mediaPath.'/img/'.$this->name;
			$this->fullFilePath = $this->docRoot.$this->webPath;
		}
    }
    
	/**
	 * Возвращает base64 закодированную картинку с ее майм типом
	 * @param type $useNoImage Присылать ли base64 картинки используемой вместо несуществующей
	 * @return array ['base64'=>string, 'mime_type'=>string]
	 */
	public function getBase64($useNoImage = false) 
	{
		$result = '';
		
		if(!$this->isExists() && !$useNoImage){
			return $result;			
		}
				
		$result['base64'] = base64_encode(file_get_contents($this->getFullPath()));
		$result['mime_type'] = $this->getMimeType();		
		
		return $result;		
	}		
	
	public function getfileInfo($file)
	{		
		if(!file_exists($file)){
			$this->errors[] = 'Файл "'.$file.'" не существует';
			return false;
		}
		
		list($width, $height, $type) = getimagesize($file);
			switch ($type) {
			case IMAGETYPE_JPEG: $typestr = 'jpg'; break;
			case IMAGETYPE_GIF: $typestr = 'gif' ;break;
			case IMAGETYPE_PNG: $typestr = 'png'; break;
			default: $typestr = null;
		}					
						
		$name = FileDataStorage::getNameWithoutExtFromFilename($file);
		$ext  = FileDataStorage::getExtFromFilename($file);		
			
		return [
			'imageType' => $typestr,
			'width'     => $width,
			'height'    => $height,
			'name'      => $name,
			'ext'       => $ext			
		];

	}
	
    public function isExists()
    {
        return (bool)$this->exists;
    }    
    
	public function getImageType()
	{		
		return $this->imageType;
	}
	
	public function getMimeType()
	{
		if(!$this->mimeType){
			$finfo = new \finfo(FILEINFO_MIME_TYPE);
			$this->mimeType = $finfo->file($this->getFullPath());
		}
		
		return $this->mimeType;
	}
	
	public function getName()
	{
		return $this->name;
	}		
	
	public function getFullPath()
	{		
		return $this->fullFilePath;
	}
			
	public function getUrl()
	{		
		return $this->webPath;
	}
	
	public function getErrors()
	{
		return $this->errors;
	}
	
	/* вырезает область изображения */
    public function crop($width, $height)
    {
		
        if(!file_exists($this->fullFilePath)){
			return new Image();
		}
		
		
		$destination_path = $this->getCachePath();
		$name = FileDataStorage::getNameWithoutExtFromFilename($this->name)
			.'_crop_'.$width.'_'.$height
			.FileDataStorage::getExtFromFilename($this->name);				
						
		if(file_exists($destination_path.'/'.$name)){
			return new Image($destination_path.'/'.$name);
		}
				
		if(!file_exists($destination_path)){
			$destination_path = FileDataStorage::makeDir($destination_path);
		}
		
		$destination_path = $destination_path.'/'.$name;				
		$imagick = new \Imagick($this->fullFilePath);
		$format = $imagick->getImageFormat();		
		
		
				
		if($format == 'GIF'){
			$imagick = $imagick->coalesceImages();
			do {
			   $imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);
			} while ($imagick->nextImage());
			$imagick = $imagick->deconstructImages();
			$imagick->writeImages($destination_path, true);			
		}else{
			$imagick->cropThumbnailImage($width, $height);
			$imagick->writeImage($destination_path);
		}				
		
		$imagick->destroy();				
		return new Image($destination_path);
    }
	
	function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100;
      return $this->resize($width,$height);
	}
	
	/**
	 * 
	 * @param integer $width
	 * @param integer $height
	 * @return \Classes\Image
	 */
	function resize($width,$height) {
		
		if(!file_exists($this->fullFilePath)){
			return new Image();
		}
		
		$source_path = $this->fullFilePath;
		$destination_path = $this->getCachePath();				
				
		$name = FileDataStorage::getNameWithoutExtFromFilename($this->name)
			.'_resize_'.$width.'_'.$height
			.FileDataStorage::getExtFromFilename($this->name);
		
		
		if(file_exists($destination_path.'/'.$name)){
			return new Image($destination_path.'/'.$name);
		}
		
		if(!file_exists($destination_path)){
			$destination_path = FileDataStorage::makeDir($destination_path);
		}
		
		$destination_path = $destination_path.'/'.$name;						
		
		$imagick = new \Imagick($this->fullFilePath);
		$format = $imagick->getImageFormat();
				
		if($format == 'GIF'){
			$imagick = $imagick->coalesceImages();
			do {
			   $imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);
			} while ($imagick->nextImage());
			$imagick = $imagick->deconstructImages();
			$imagick->writeImages($destination_path, true);
		}else{
			//$imagick->thumbnailimage($width, $height);
			$imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS,1);
			$imagick->writeImage($destination_path);
		}						
		
		$imagick->destroy();
						
		return new Image($destination_path);
	}
    
	public function getCachePath()
	{		
		return $this->docRoot		
		.$this->fileCacheDir
		.FileDataStorage::getWordsStructureFromFileName($this->name)
		.'/'.md5($this->fullFilePath);		
	}	    
	
	public function clearCache()
	{
		$dir = $this->getCachePath();			
		if(file_exists($dir)){
			$files = array_diff(scandir($dir), array('.','..')); 
			foreach ($files as $file) { 				
			  unlink("$dir/$file"); 
			} 			
			return rmdir($dir);
		}
		
		return true;
	}	
	
	/**
	 * Закачивает загруженный файл
	 * @param array $uploadData  - массив данных загруженного файла из $_FILE
	 * @param string $dest Путь к папке относительно папки upload_files
	 * @param string $newName имя сохраненного файла без расширения
	 * @param boolean $rewrite Переписать файл? , если false - добавляется индекс file__1, file__2
	 * @return boolean
	 * 
	 */
	public function upload(array $uploadData, $destination, $newName='', $rewrite = false)
	{
		$destination = FileDataStorage::normalizePath($destination);
		$newName = basename($newName);
		
		$fromFilePath = isset($uploadData['tmp_name'])? $uploadData['tmp_name'] : '';
		
		$info = $this->getfileInfo($fromFilePath);
		if(!$info){
			return false;
		}
		
		if(null === $info['imageType']){
			$this->errors[] = 'Формат файла "'.$file.'" не поддерживается';
			return false;
		}					
		
		
		$newName = $newName? $newName : $info['name'];
		$newName = \translit($newName);		
		
		$nameDest = $newName;
				
		if(substr($destination, 0, 1) !== '/'){
			
			$destination = $this->getUploadDir().'/'.$destination;			
		}
		
		if(!file_exists($destination)){
			FileDataStorage::makeDir($destination);
		}
						
		if(!$rewrite){
			$index = 0;			
			while(file_exists($destination.'/'.$nameDest.'.'.$info['imageType'])){				
				$this->errors[] = 'Файл уже существует';
				return false;
			}
		}				
		
		$nameDest = $nameDest.'.'.$info['imageType'];
		
		if(!move_uploaded_file($uploadData['tmp_name'], $destination.'/'.$nameDest)){
			$this->errors[] = 'Не удается загрузить файл';
			return false;
		}
						
		$this->fullFilePath = $destination.'/'.$nameDest;		
		$this->name = $nameDest;
		$this->webPath = strpos($destination.'/'.$nameDest, $this->docRoot) === 0 ? str_replace($this->docRoot, '',$this->fileUplDir.$destDir.'/'.$nameDest) : '';
		
		$this->exists = true;		
		
		// Удаляем кэш
		FileDataStorage::removeDirectory($this->getCachePath());	
		
		return $this;		
	}
	
	public function getUploadDir()
	{
		return FileDataStorage::normalizePath($this->docRoot.$this->fileUplDir);
	}
	
	public function getWebDir()
	{
		return $this->fileUplDir;
	}
	
	public static function setUploadDir($dir)
	{
		self::$_fileUplDir = $dir;
	}
	
	public static function setCacheDir($dir)
	{
		self::$_cacheDir = $dir;
	}
	
	public static function setMediaPath($path)
	{
		self::$_mediaPath = $path;
	}
}

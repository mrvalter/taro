<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services;
/**
 * @category MED CRM
 */
class FileDataStorage {
		
	public static function makeDir($path)
	{
		
		$path = self::normalizePath($path);
		mkdir($path, 0755, true);			
		return $path;
	}
	
	public static function normalizePath($path)
	{
		if(substr($path, 0, 1)!= '/')
			$path = '/'.$path;
		
		if(substr($path, -1) == '/')
			$path = substr($path, 0, -1);
				
		return $path;
	}
	
	/**
	 * Возвращает структуру папок в зависимости от названия файла
	 * Например (name.jpg) вернет /n/na/name.jpg
	 * @param string $fileName Имя файла
	 * @return string $path 
	 * 
	 */
	public static function getWordsStructureFromFileName($fileName)
	{		
		$fileName = basename($fileName);
		
		if(!$fileName){
			return '';
		}
		
		$firstS = strtolower(substr($fileName, 0, 1));
		$twoS   = strtolower(substr($fileName, 0, 2));
		
		$path = "/$firstS/$twoS";
		
		return $path;
	}
	
	/**
	 * @TODO Ужасная логика - исправить!!!
	 */
	public static function getNameWithoutExtFromFilename($filename)
	{
		$filename = basename($filename);
		
		$ext = substr($filename, -4);
		return substr($ext, 0, 1)!='.' ? $filename : substr($filename, 0, -4);
	}
	
	/**
	 * @TODO Ужасная логика - исправить!!!
	 */
	public static function getExtFromFilename($filename)
	{
		$filename = basename($filename);
		
		$ext = substr($filename, -4);
		return substr($ext, 0, 1)=='.' ? $ext : '';			
	}
	
	public static function removeDirectory($dir) 
	{
		if(!file_exists($dir)){
			return true;
		}
		
		if ($objs = glob($dir."/*")) {
			foreach($objs as $obj) {
				is_dir($obj) ? self::removeDirectory($obj) : unlink($obj);
			}
		}
		return rmdir($dir);		
	}
		
}

<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 * @category MED CRM
 */
namespace Services\Interfaces;

/**
 *
 * Интервейс шаблонизатора 
 */
interface ViewInterface {
    
    /**
     * Возвращает сгенерированный HTML 
     * @param string $template Имя используемого темплейта
     * @param array $params передаваемые параметры
     * @return string;
     */
    public function render($template, $params=array(), $dir=null);
    
    /**
     * Возвращает окончание файлов шаблонов и темплейтов
     * @return string 
     */
    public function getFileExtension();    

    /**
     * Возвращает название используемого шаблона
     * @return string;
     */
    public function getLayout();
    
    /**
     * Возвращает название текущей кодировки
     * @return string;
     */
    public function getCharset();
            
    /**
     * Возвращает название текущего языка
     * @return string;
     */
    public function getLang();
    
    /**
     * Устанавливает окончание файлов шаблонов и темплейтов
     * @param string $ext
     * @return \ViewInterface
     */
    public function setExtension($ext);    
    /**
     * Устанавливает Шаблон страницы
     * @param string $name
     * @return \ViewInterface
     */
    public function setLayout($name);
    
    /**
     * Устанавливает кодировку страницы в шапке
     * @param string $charset
     * @return \ViewInterface
     */
    public function setCharset($charset);
    
    /**
     * Устанавливает язык страницы в шаблоне.  <html lang="ru">
     * @param string $lang
     * @return \ViewInterface
     */
    public function setLang($lang);
    
    /**
     * Устанавливает путь к директории с шаблонами
     * @param string $path
     * @return \ViewInterface
     */
    public function setLayoutPath($path);
    
    /**
     * Устанавливает путь к директории с темплейтами
     * @param string $path
     * @return \ViewInterface
     */
    public function addTemplatePath($path);
    
    
}

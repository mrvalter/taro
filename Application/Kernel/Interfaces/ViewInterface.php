<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 * @category MED CRM
 */
namespace Kernel\Interfaces;

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
    public function render(string $template, array &$params=[], string $namespace = ''): string;
    
    /**
     * Возвращает окончание файлов шаблонов и темплейтов
     * @return string 
     */
    public function getFileExtension();    
   
    
    /**
     * Устанавливает путь к директории с темплейтами
     * @param string $path
     * @return \ViewInterface
     */
    public function addTemplatePath($path, $namespace = '');
    
    
}

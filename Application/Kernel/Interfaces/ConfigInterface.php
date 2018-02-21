<?php
namespace Kernel\Interfaces;

interface ConfigInterface {
	
	/**
     * Возвращает значение по ключу или null если зачения нет
	 * Без указания каких либо значений возвращает полный конфиг
     * @param ... ключи конфига
     * @return mixed | null
     */
	public function getValue(...$keys);
	
	/**
     * Возвращает  теги для замены
     * @return array
     */
	public function getTags(): array;
	
	
	/**
	 * Возвращает переменную окружения (prod, dev)
	 */
	public function getEnviroment(): string;
	
	
	/**
	 * Добавляет конфиг по имени файла или имени папки с конфигами
	 * 
	 * @param string $file Путь до файла
     * @param string $key Ключ в конфигурационном массиве
     * @param boolean $required Вызывыть исключение, если файла нет. По умолчанию TRUE
	 */
	public function addFile(string $file, bool $required, string $key): ConfigInterface;
	
}

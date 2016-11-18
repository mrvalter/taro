<?php
/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 * @category MED CRM
 */

/**
 * Класс исключения в объекте конфигурации \Services\Config
 */
class SystemErrorException extends AppException{
    
    public function __construct(string $message = "", string $sysmessage = '', int $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $sysmessage, $code, $previous);
    }
}

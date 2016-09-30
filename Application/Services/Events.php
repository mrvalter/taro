<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services;

use Classes\ADUser as ADUser;

/**
 * @category MED CRM
 */
class Events {
	
	private static $ignore = false;
	private static $transaction = false;
	private static $events=[];
	
	protected $db;
	protected $config;
	protected $mailer;
	protected $initiator;	
	
	public function __construct(DB $db, Config $config, Mailer $mailer, Security $security)
	{
		$this->db = $db;
		$this->config = $config;
		$this->mailer = $mailer;
        $this->initiator = $security->getUser();
		
	}		
	
	/**
	 * 
	 * @param string $db
	 * @return \Services\DB\PDODriver
	 */
	public function getPDOFrom($db)
	{
		return $this->db->getDBConn($db)->getPdo();
	}
	
	
	public function add($name, $params=[])
	{	
		if(!self::$ignore){
			self::$events[] = [$name, $params];
		}
				
		if(!self::$transaction){            
			$this->execute();
		}				
	}
	
	private function execute()
	{        
		if(!isset(self::$events[0])){
			return;
		}
		
		try {
			foreach(self::$events as $i=>$event){
				$name = $event[0];
				$params = $event[1];
				
				if(method_exists($this, $name) && is_callable([$this, $name], false, $callable_name)){
					call_user_func_array([$this, $name], $params);
				}				
			}
            self::$events = [];
		}catch(\Exception $e){
			$this->SYSTEM_ERROR('Не удалось выполнить евент "'.$name.'"', $e);
		}
	}    	
	
	public static function push($name, $params)
	{        
		\App::o()->getService('events')->add($name, $params);
	}
	
	public static function startIgnore()
	{
		self::$ignore = true;
	}
	
	public static function stopIgnore()
	{
		self::$ignore = false;
	}
    
	public static function beginTransaction()
	{
		self::$transaction = true;
	}
	
	public static function commit()
	{
		self::$transaction = false;
		$this->execute();
	}
	
	public static function clear()
	{
		self::$events = [];
	}
	
	public function SYSTEM_ERROR($message='Системная ошибка', \Exception $exception=null)
    {		        
        /* Отправляем по почте сообщение об ошибке на почту администраторов */        
        $adminMails = $this->config->get('admin_mails');
        if(!sizeof($adminMails)){
            return;
        }
		
        $mail = $this->mailer;
        foreach($adminMails as $adminMail){
            $mail->addAddress($adminMail, 'admin');
        }
                
        $mail->Subject = 'Произошла ошибка Евента на сайте';
        $mail->AltBody = $message.($exception ? ' '.$exception->getTraceAsString() : '');
        $mail->msgTemplate('system_error', [
            'message'   => $message, 
            'exception' => $exception,
            'initiator' => $this->initiator
        ]);
                
        @$mail->send();

    }
}

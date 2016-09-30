<?php
namespace Services;

include_once dirname(__FILE__).'/PHPMailer/class.phpmailer.php';
/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */


/**
 * @category MED CRM
 */
class Mailer extends \PHPMailer{
		
	public function __construct($config, $env='prod')
	{		
		parent::__construct(false);
		
		$language = isset($config['language']) ? $config['language'] : 'ru';
		$this->setLanguage($language, dirname(__FILE__).'/PHPMailer/language/');				
		$this->setFrom($config['from_email'], $config['from_name']);
		
		$this->CharSet = 'UTF-8';
		switch($config['type']){
			case 'smtp':
				$this->isSMTP();
				$this->Host = $config['host'];
				$this->Port = $config['port'];				
				break;
			case 'sendmail':
				$this->isSendmail();
				break;
		}
		
	}		
	
	public function msgHTML($message, $basedir='', $advanced=false)
    {
		parent::msgHTML($message, $_SERVER['DOCUMENT_ROOT']);
	}
	
	public function msgTemplate($template, $params=[])
	{				
		$message = \App::insert('mail', 'rendermail', [
			'template'=>$template,
			'params' => $params	
			])->renderPage()->getPage();
		$this->msgHTML($message);
	}
}

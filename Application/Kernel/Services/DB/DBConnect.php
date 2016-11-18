<?php
/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 * @category MED CRM
 */
namespace Kernel\Services\DB;


/**
 * Класс работы с базой данных.
 * 
 */
abstract class DBConnect {
       
    /** @var object ссылка на соединение с базой данных */
	private $_link    = null;
    /** @var  PDODriver ссылка на драйвер PDO */
	private $_pdo = null; //class PDODriver    
    
	/** @var string адрес хоста */
	protected $_host;
	
	protected $_port;
	/** @var string имя пользователя БД */
	protected $_user;
    /** @var string пароль к БД */
	protected $_password;
    /** @var string название базы данных */
	protected $_dbname;
    /** @var string кодировка соединения с БД */
	protected $_encoding;    		
	
    /**
     * 
     * @param string $user Имя пользователя
     * @param string $password Пароль
     * @param string $host Адрес хоста
     * @param string $encoding Кодировка соединения с базой данных
     * @param string $dbname Имя базы данных
     */
    public function __construct($user, $password, $host, $encoding, $dbname, $port='')
    {        
        $this->_host     = $host;
        $this->_user     = $user;
        $this->_password = $password;        
        $this->_encoding = $encoding;
        $this->_dbname   = $dbname;	
		$this->_port     = $port;
    }
        
    /**
     * Возвращает ссылку соединения с базой данных
     * @return object 
     */
    public function getLink()
    {        
        if(null === $this->link){
            $this->_link = $this->createLink();
        }
        return $this->_link;
    }
    
	/**
	 * Создает и возвращает новый коннект к БД
	 * @return object
	 */
    public function getNewLink()
    {
        return $this->createLink();
    } 
	
	/**
	 * @return \PDO
	 */
	public function getPdo()
	{
		return null !== $this->_pdo ? $this->_pdo : $this->_pdo = $this->createPdo();
	}
	
	public function getDbName(){
		return $this->_dbname;
	}
	
	abstract protected function createLink();	
	/**
	 * @return \Services\DB\PDODriver
	 */
	abstract protected function createPdo();	   
}
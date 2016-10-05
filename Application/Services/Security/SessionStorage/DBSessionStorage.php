<?php
namespace Services\Security\SessionStorage;
use Services\Security\SessionStorage\NativeSessionStorage;
use \PDO;

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */

/**
 * @category MED CRM
 */
class DBSessionStorage extends NativeSessionStorage implements \SessionHandlerInterface {
    
    
    /** @var PDO*/
    private $pdo;
        
    private $write_sess = true;
    private $isNew;
        
    public function __construct(\PDO $pdo, $login, $domen)
    {
        parent::__construct($login, $domen);

        $this->pdo = $pdo;                		
    }
             
    
    public function destroy($session_id)
    {
        $sql = 'DELETE FROM Session WHERE sess_id=:id AND login=:login AND domen=:domen';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $session_id, \PDO::PARAM_STR);
        $stmt->bindValue(':login', $this->login, \PDO::PARAM_STR);
        $stmt->bindValue(':domen', $this->domen, \PDO::PARAM_INT);
        $stmt->execute();
        return true;
    }        
   
    public function open($save_path, $name)
    { 
        return true;
    }
    
    /**
     * 
     * @param type $session_id
     * @return array Массив Сессии
     * @todo Записывать в лог, если фильтр не подходит - попытка взлома.
     */
    public function read($session_id) 
    {        
        
        $stmt = $this->pdo->prepare('
            SELECT last_visit, login, domen, data
            FROM Session 
            WHERE sess_id=:id');
        
        $stmt->bindValue(':id', $session_id, \PDO::PARAM_STR);
        
        $arr = $stmt->fetch(\PDO::FETCH_LAZY);
                
        if(!$arr){
            $this->isNew = true;
            return '';
        }        
        
        $data = base64_decode($arr->data, MCRYPT_MODE_ECB);
        if($this->isAuthorized() && ($this->login != $arr->login || $this->domen != $arr->domen))
        {
            $this->create_sid();
            return '';
        }
        
        $this->login = $arr->login;
        $this->domen = $arr->domen;
        
        return $data;
    }
    
    public function write($session_id, $session_data) 
    {

        if(!$this->isAuthorized){
            return true;
        }        
        
        $data = base64_encode($session_data);
        
        if ($this->isNew ){
            $sql = 'INSERT INTO Session SET sess_id=:sess_id, login=:login, domen=:domen, data=:data, last_visit=:last_visit ';
        }else{
            $sql = 'UPDATE Session SET data=:data, last_visit=:last_visit WHERE login=:login AND domen=:domen AND sess_id=:sess_id';
        }        

        
        $sth = $this->pdo->prepare($sql);
        $sth->bindValue(':login',      $this->login,        \PDO::PARAM_STR );
        $sth->bindValue(':domen',      $this->domen,        \PDO::PARAM_STR );
        $sth->bindValue(':data',       $data,               \PDO::PARAM_STR );
        $sth->bindValue(':last_visit', time(), \PDO::PARAM_STR );                    
        $sth->bindValue(':sess_id',    $session_id,         \PDO::PARAM_STR );        
        $sth->execute();
                
        return true;        
    }       
            
    public function gc($maxlifetime) 
    {
        $sql = 'DELETE FROM Session WHERE last_visit < '.(time () - $maxlifetime);        
        $this->pdo->exec($sql);
        
        return true;
    }
    
    public function create_sid() 
    {
        session_regenerate_id(true);
        $this->session_id = session_id();
    }
    
    public function close() 
    {
   
    }
   
   /**
    * Устанавливает значение записывать или нет
    */
    public function setWriteMode($mode = false)
    {
        $this->write_sess = $mode;
        return $this;
    }
   
    public function isDestroy()
    {
       return $this->isDestroied;
    }
   
    public function start()
    {  
        if($this->sessionStarted){
            return $this;
        }
        
        session_set_save_handler( 
        array($this, 'open'), 
        array($this, 'close'), 
        array($this, 'read'), 
        array($this, 'write'), 
        array($this, 'destroy'), 
        array($this, 'gc'));
        
        return parent::start();
    }
   
}

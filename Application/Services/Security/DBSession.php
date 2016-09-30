<?php

namespace Services\Security;
use Services\DB as DB;

class DBSession extends Session implements \SessionHandlerInterface {
    
    //private $storage;
    
    private $db;

    private $session_id;       
    private $isNew=false;
    private $write_sess = true;
    private $isDestroy = false;
        
    public function __construct(DB $db, $login, $domen)
    {
		parent::__construct($login, $domen);
		
		$this->db = $db;                		
    }
             
    
    public function destroy($session_id) 
    {
        $sql = 'UPDATE Session SET is_destroy = 1 WHERE sess_id=:id AND login=:login AND domen=:domen';
        $pdoDrv = $this->db->getPDO();
        $sth = $pdoDrv->prepare($sql);
        $sth->execute([':id'=>$session_id, ':login'=>$this->login, ':domen'=>$this->domen]);
        return true;
    }
    
    public function undestroy()
    {
        $sql = 'UPDATE Session SET is_destroy = 0 WHERE sess_id=:id AND login=:login AND domen=:domen';
        $pdoDrv = $this->db->getPDO();
        $sth = $pdoDrv->prepare($sql);
        $sth->execute([':id'=>$this->session_id, ':login'=>$this->login, ':domen'=>$this->domen]);
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
    public function read($session_id) {
        $data = '';
        $pdoDrv = $this->db->getPDO();       
        
        $sth = $pdoDrv->prepare(
            'SELECT last_visit, data, is_destroy '
          . 'FROM Session '
          . 'WHERE login=:login AND domen=:domen AND sess_id=:sess_id');                
        
        $sth->execute([':login'=>$this->login, ':domen'=>$this->domen, ':sess_id'=>$session_id]);
        $arr = $sth->fetch(\PDO::FETCH_ASSOC);
                
        if(!$arr){
            $this->isNew = true;
            return '';
        }        
            
        $data = base64_decode($arr['data'],MCRYPT_MODE_ECB);
        $this->isDestroy = $arr['is_destroy'];
        return $data;
    }
    
    public function write($session_id, $session_data) 
    {

        if(!$this->write_sess){
            return true;
        }        
        
        $data = base64_encode($session_data);
        
        if ($this->isNew ){
            $sql = 'INSERT INTO Session SET sess_id=:sess_id, login=:login, domen=:domen, data=:data, last_visit=:last_visit ';
        }else{
            $sql = 'UPDATE Session SET data=:data, last_visit=:last_visit WHERE login=:login AND domen=:domen AND sess_id=:sess_id';
        }        

        $pdoDrv = $this->db->getPDO();
        $sth = $pdoDrv->prepare($sql);        
        $sth->bindValue(':login',      $this->login,        \PDO::PARAM_STR );
        $sth->bindValue(':domen',      $this->domen,        \PDO::PARAM_STR );
        $sth->bindValue(':data',       $data,               \PDO::PARAM_STR );
        $sth->bindValue(':last_visit', date('Y-m-d H:i:s'), \PDO::PARAM_STR );                    
        $sth->bindValue(':sess_id',    $session_id,         \PDO::PARAM_STR );        
        $sth->execute();
                
        return true;        
    }       
    
    
    
    public function gc($maxlifetime) 
    {
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
    * Устанавливает значение записывать сессию в false
    */
   public function setNotWrite()
   {
       $this->write_sess = false;
	   return $this;
   }
   
   public function isDestroy()
   {
       return $this->isDestroy;
   }             
   
}

<?php
namespace Services;
use \Services\Security\Session as Session;
use \Services\Security\DBSession as DBSession;
use \Services\Menu\MenuCollection as MenuCollection;
use \Classes\ADUser as ADUser;

class Security_old {
    
    const SESSION_DB = 'db';
    const SESSION_DEFAULT = 'default';
	
    protected $db;
    protected $dbOffice;
    protected $user = null;
    protected $router = null;
    protected $session = null;
	protected $sessionType = null;
    
    protected $menuService = null;        	
	protected $rightsByUri = [];
    
    protected $rightsHdbk;
    
    
    public function __construct(DB $db, Router $router, $session_type='default', $rightsHdbk=[])
    {       
        
        $this->rightsHdbk = $rightsHdbk;
        $this->db = $db->getDBConn('db');
        $this->dbOffice = $db->getDBConn('dbOffice');
        $this->router = $router;        
        $this->user = new ADUser();
        //var_dump($params);
		switch($session_type){
			case self::SESSION_DB:
				$this->sessionType = self::SESSION_DB;
				break;

			default : 
				$this->sessionType = self::SESSION_DEFAULT;
		}		
		
		$this->sessionStart();
    }   
	
	public function getUser()
	{
		return $this->user;
	}
    
	public function getMenuRightsByMenuId($menuId)
	{
		$user = $this->user;
		$pdo = $this->db->getPDO();
		$stmt = $pdo->query("call PageMenu_Rights('".$user->sAMAccountName.
               "', ".$user->Domen.", $menuId, 0)");
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
					
		return $row['Rights'];		
	}
	
	public function getRightsByRouter(Router $router)
	{
		$path = strtolower($router->getPageMenuUrl());		
				        
		if(!isset($this->rightsByUri[$path])){
			$menuRightsCollection = $this->menuService->getItemByPath($this->user, $path);			
			$this->rightsByUri[$path] = $menuRightsCollection;
		}
				
		return $this->rightsByUri[$path];
	}		
	
	public function getSession()
    {
        return $this->session;
    }	   
        	
    private function sessionStart()
    {
        $login = $domen = '';
        
        if(isset($_SERVER['REMOTE_USER'])) {
            if (strpos($_SERVER['REMOTE_USER'], "@")>0) {
                $login = substr($_SERVER['REMOTE_USER'],0,strpos($_SERVER['REMOTE_USER'], "@"));
                $domen = $this->getDomenFromRemoteUser($_SERVER['REMOTE_USER']);
            } else {
                $login = $_SERVER['AUTHENTICATE_SAMACCOUNTNAME'];
                $domen = $this->getDomenFromRemoteUser($_SERVER['REMOTE_USER']);
            }
        }                        
		
		switch($this->sessionType){
			case self::SESSION_DB:		
				$session = new DBSession($this->dbOffice, $login, $domen);
				session_set_save_handler( 
				array($session, 'open'), 
				array($session, 'close'), 
				array($session, 'read'), 
				array($session, 'write'), 
				array($session, 'destroy'), 
				array($session, 'gc'));
				break;
			
			default:
				$session = new Session($login, $domen);
				
		}
		
        $session->start();                                
        $this->session = $session;
    }
    public function authorise()
    {    		
        $login = $this->session->getLogin();
        $domen = $this->session->getDomen();        
        if(!$login || !$domen){
            $this->session->setNotWrite();
            return false;
        }		
        
        /* если произошел логаут */
        $is_Mobile = $request = $this->router->getRequest()->isMobile();
        
        if($this->session->isDestroy() && $is_Mobile){
            return false;
        }
        
        $user = $this->getUserByLoginDomen($login, $domen);
        		
        if(!$user->isExists()){
            return false;
        }
        
        $this->user = $user;		        
		
        $_SESSION['_USER']['sAMAccountName'] = $user->sAMAccountName;
        $_SESSION['_USER']['Domen'] = $user->Domen;
        $_SESSION['_USER']['DomenAlias'] = $user->DomenAlias;
        $_SESSION['_USER']['UID'] = $user->UID;
		
		//$this->menuService->initMenu($user);
	
        return true;
        
    }		
    
    public function authentificate($login, $password)
    {
		
        $user = $this->getUser();
		
        /*if($user->isExists()){
            if($user->sAMAccountName != $login){
                return false;
            }
        }*/
          
		
        $userData = $this->LDAPAuthUser($login, $password);
		
        if(!$userData){        
            return false;
        }                          
        
        $domen = $userData[1];
        $this->user = $this->getUserByLoginDomen($login, $domen);        
        //$this->session->undestroy();
        return true;
        
    }
    
    /**
     * 
     * @param string $login
     * @param string $domen
     * @return \ADUser
     */
    public function getUserByLoginDomen($login, $domen)
    {
        $ADUserRep = new \ADUserRepository();
        return $ADUserRep->getUserByLoginDomen($login, $domen);
		//return $adUser->setRoledUser($roleUser);
		
		
		
    }
    
    public function getDomenFromRemoteUser($remoteUser)
    {        
        
        if (strpos($remoteUser, "@")>0) {
            $Domen=substr($remoteUser,strpos($remoteUser, "@")+1);
        } else {
            $Domen=substr($remoteUser,strpos($remoteUser, "DC=")+3);
        }
        
        return $Domen;                        

    }
	
	public function setMenuService(Menu $menu)
	{
		$this->menuService = $menu;
	}
	
    public function LDAPAuthUser($login, $password)
    {
        $login = trim($login);
        if(!$login || !$password){
            return false;
        }

        $result = array();

        $adServers = array(
          "ldaps://materiadc.moscow:636",
          "ldap://1c.chel"
        );
        $adDomens = array(
          'MOSCOW',
          'CHEL'
        );
        for($s=0;$s<2;$s++){
            $ldap = \ldap_connect($adServers[$s]);
            
            $username = $login;
            $password = $password;        

            $ldaprdn = $adDomens[$s] . "\\" . $username;
            
            \ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
            \ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
            
            $bind = @\ldap_bind($ldap, $ldaprdn, $password);
			
            if ($bind) {           
                $filter="(sAMAccountName=$username)";

                $result = \ldap_search($ldap,"dc=".$adDomens[$s],$filter);

                \ldap_sort($ldap,$result,"sn");
                $info = ldap_get_entries($ldap, $result);
                for ($i=0; $i<$info["count"]; $i++)
                {
                    $result = array($info[$i]["samaccountname"][0], (string)($s+1), $info[$i]["sn"][0], $info[$i]["givenname"][0]);
                }
                @\ldap_close($ldap);            
                break;
            }else{
                continue;
            }         
        }

        if(!isset($result[0])){
            return false;
        }else{
            return $result;
        }
    }        
    
	/**
	 * 
	 * @param \Services\Router $router
	 * @return null | MenuItem
	 */
    public function checkAccess(Router $router)
    {        				
        $Rights = $this->getRightsByRouter($router);		
		if($Rights){						
			if($Rights->getRight() != ''){				
				return $Rights;
			}	
		}
		
		
		
		return null;
	}
	
	public function buildAllMenuRights(Menu $menu)
	{
		return $menu->buildMenu();
	}
	
	public function getRightsHdbk()
	{
        if(sizeof($this->rightsHdbk)){
            return $this->rightsHdbk;
        }
        
		return [
			(object)['value'=>'',  'label'=>'Нет',    'bg_class'=>'bg-success'],
			(object)['value'=>'R', 'label'=>'Чтение', 'bg_class'=>'bg-primary'],
			(object)['value'=>'W', 'label'=>'Запись', 'bg_class'=>'bg-danger'],
		];
	}
	
	public function accessDeniedGenerate()
	{
		header("HTTP/1.0 403 Access Denied");
		$content = \App::insert('index', 'accessDenied')->getContentHTML();
		\App::o()->getDefaultView()->setContentHTML($content)->renderPage()->showPage();                
		die();
	}
}
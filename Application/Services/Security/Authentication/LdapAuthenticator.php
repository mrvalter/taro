<?php

namespace Services\Security\Authentication;
use Services\Security\Interfaces\AuthenticatorInterface;
use Services\Security\Interfaces\UserRepositoryInterface;

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */

/**
 * @category MED CRM
 */
class LdapAuthenticator implements AuthenticatorInterface{
    
    private $userRepository;
    private $ldap;
    
    public function __construct(UserRepositoryInterface $userRepository, $ldap)
    {        
                                
        $this->userRepository = $userRepository;
        $this->ldap = $ldap;
    }
    
    /**
     * 
     * @param string $login
     * @param string $password
     * @param string $domen
     * @return boolean | UserRepositoryInterface
     */
    public function authenticate($login, $password, $domen = null) 
    {
        
        if(!$login || !$password){
            return false;
        }                                 

        $ldaprdn = $adDomens[$s] . "\\" . $login;

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
        }
        
        if(!isset($result[0])){
            return false;
        }
        
        $domen = $userData[1];
        $user = $this->userRepository->getUserByLogin($login, $domen);
        if(!$user->isExists()){
            return false;
        }
        
        return $user;    
    }
    
}
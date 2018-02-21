<?php
use Kernel\Services\Security\SessionStorage\NativeSessionStorage;
use Classes\UserRepository;
use Kernel\Services\Security\Authentication\DbAuthenticator;
use Kernel\Services\Security\Authentication\LdapAuthenticator;
use Kernel\Services\MenuBuilder_MM\MenuBuilder;
use Kernel\Services\Security\Security;
use Kernel\Services\Viewer\Vi;
use Kernel\Services\DB;
use Kernel\Interfaces\ConfigInterface;
use Kernel\Services\Router\Router;
use Kernel\Services\Firewall\Firewall;

/**
 * @property-read NativeSessionStorage $session_storage Сессия
 * @property-read UserRepository $user_repository
 * @property-read DbAuthenticator $authenticator
 * @property-read LdapAuthenticator $authenticatorLdap
 * @property-read MenuBuilder $menu_builder
 * @property-read Security $security
 * @property-read Vi $viewer
 * @property-read DB $database
 * @property-read ConfigInterface $config;
 * @property-read Router $router;
 * @property-read Firewall $firewall;
 * 
 * 
 */
class ServiceContainer extends Kernel\Classes\ServiceContainer {
		
	public function __get($name){
		return $this->get($name);
	}
	
	public function Service_database()
	{
		
		
	}
	
}

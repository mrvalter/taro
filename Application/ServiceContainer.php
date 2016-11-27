<?php

use Kernel\Services\ServiceContainerReal;
use Kernel\Services\Security\SessionStorage\NativeSessionStorage;
use Classes\UserRepository;
use Kernel\Services\Security\Authentication\DbAuthenticator;
use Kernel\Services\Security\Authentication\LdapAuthenticator;
use Kernel\Services\Security\Security;
use Kernel\Services\Viewer\TwigViewer;

class ServiceContainer extends ServiceContainerReal {
      
    
    public function session_storage(): NativeSessionStorage
    {
        return $this->getService('session_storage');
    }
    
    
    public function user_repository(): UserRepository
    {
        return $this->getService('user_repository');
    }
    
    
    public function authenticator(): DbAuthenticator
    {
        return $this->getService('authenticator');
    }
    
    
    public function authenticatorLdap(): LdapAuthenticator
    {
        return $this->getService('authenticatorLdap');
    }
    
    
    public function security(): Security
    {
        return $this->getService('security');
    }
    
    
    public function viewer(): TwigViewer
    {
        return $this->getService('viewer');
    }
    
        
}
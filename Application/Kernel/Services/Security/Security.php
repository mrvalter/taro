<?php

namespace Kernel\Services\Security;
use Kernel\Services\Security\Authentication\AuthenticationManager;
use Kernel\Services\Security\Interfaces\AuthenticatorInterface;
use Kernel\Services\Security\Csrf\CsrfManager;

use Kernel\Services\Security\Interfaces\SessionStorageInterface;
use Kernel\Services\Security\Interfaces\UserRepositoryInterface;
use Kernel\Services\Security\Interfaces\MenuBuilderInterface;
use Psr\Http\Message\RequestInterface;


/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */

/**
 * @category MED CRM
 */
class Security {
        
    private $authenticationManager;
    private $csrfManager;
    private $sessionStorage;
    private $userRepository;
    private $menuBuilder;
    private $user;    
    
    public function __construct(        
        AuthenticatorInterface $authenticator,              
        UserRepositoryInterface $userRepository,
        SessionStorageInterface $sessionStorage,
        MenuBuilderInterface $menuBuilder
    ) {
                
        $this->authenticationManager = new AuthenticationManager ($authenticator, $sessionStorage);
        $this->csrfManager = new CsrfManager();
        $this->userRepository = $userRepository;
        $this->sessionStorage = $sessionStorage->start();
        $this->menuBuilder = $menuBuilder;
        $this->user = null;
    }
    
    public function authorize()
    {
                
        if(!$this->sessionStorage->isAuthorized()){
            return false;
        }
        
        $user = $this->userRepository->getUserByLogin(
            $this->sessionStorage->getLogin(), 
            $this->sessionStorage->getDomen()
        );
        
        if(!$user->isExists()){
            return false;
        }
        
        $this->user = $user;
        
        return true;
    }
    
    public function isAuthorized()
    {
        return $this->user !== null;
    }   
    
    /**
	 * @TODO RIGHTS
	 * @param RequestInterface $request
	 * @return type
	 */
    public function getRights(RequestInterface $request)
    {
        $path = $request->getUri()->getPath();
        $this->menuBuilder->getMenuCollection()->getByUrl($path);
        
        return [];
    }
    
    public function getMenu()
    {
        return $this->menuBuilder->getMenuCollection();
    }
}

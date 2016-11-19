<?php
namespace Kernel\Classes;
/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 * @category MED CRM
 */

use Kernel\Services\Interfaces\ViewInterface;
use Kernel\Services\HttpFound\Uri;
use Kernel\Services\Router;
use Kernel\Services\DB\PDODriver;
use Kernel\Interfaces\{ControllerInterface};
use Kernel\Services\Firewall;
use Kernel\Services\Security\Interfaces\UserInterface;


use ServiceContainer;


/**
 * Основной класс контроллера
 *
 * 
 */
abstract class Controller implements ControllerInterface {     
    
	const defaultActionName = 'index';
	const actionPostfix     = 'Action';
	private static $rights = [];
	
	public $title;		
	
	private $className = '';
	private $bundleName;
	private $controllerName;
	
	private $serviceContainer = null;
	private $uri = null;
    
	
	
    public function __construct(ServiceContainer $serviceContainer, Uri $uri)
	{
		$this->uri = $uri;
        $this->className = get_class($this);
		$this->_initNames(); 
		$this->serviceContainer = $serviceContainer;
    }    								
	
	public function _runController()
	{	
		$pathParams = $this->_getPathParts();		
		$action = ($pathParams[0] ?? self::defaultActionName).self::actionPostfix;
		
		if(!is_callable([$this, $action])){
			throw new \ControllerMethodNotFoundException('', "Не найден метод $action контроллера ". get_class($this));
		}
		
		return $this->callAction($this, $action, array_slice($pathParams, 1));
	}
   
	protected static function callAction(ControllerInterface $oController, string $action, array $params=[])
	{
		var_dump(get_class($oController));
		var_dump($action);
		var_dump($params);
		var_dump('call action Controller');
		die();
	}				
	
    /**
     * Возвращает объект PDO
     */
    final protected function getPDO(string $db): PDODriver
    {
		return $this->getService('db')->getDBConn($db)->getPdo();
    }

	
    /**
     * Возвращает объект Пользователя ADUser
     * @return UserInterface
     */
    final protected function getUser(): UserInterface
    {
		return $this->getFirewall()->getSecurity()->getUser();
    }
    		
	
    /**
     * Возвращает объект Объект класса сервиса показа шаблонов
     * @return \Services\View 
     */
    public function getView()
    {
        return $this->View;
    }                          
    
	/**
    * Возвращает массив конфига по ключу
    * @param type $config
    * @return array
    */
   public function getConfig($config='')
   {
		return $this->get('_config')->get($config);
   }

   /**
    * Возвращает сервис Меню
    * @return \Services\Menu
    */
   public function getMenu()
   {
		return $this->serviceContainer->get('menu');
   }

   /**
    * Возвращает Объект Меню, относящийся к этому контроллеру и экшену,
    * с правами и Опшинами
    * @return UserInterface
    */    
   public function getRights()
   {
           return $this->rights;
   }

   /**
    * Возвращает Опшен по имени в menu_url, либо null, если не найден
    * @param string $name
    * @return mixed MenuItem | null
    */
   public function getOptionByName($name)
   {		

           $childs = $this->rights->getChilds();		
           $childs->rewind();

           if($childs->valid()){
                   foreach($childs as $child){
                           if($child->menu_url == $name){										
                                   return $child;
                           }
                   }
           }

           return null;
   }		

   /**
    * Вовзращает Объект запроса на сервер, или null 
    * если Роутер был создан не из Запроса
    * @return mixed Services\HttpFound\Request || null
    */	
    public function getRequest()
    {
        return $this->get('router')->getRequest();
    }        		
    
    /**
     * 
     * @param string $title
     * @return \Classes\Controller
     */
    public function setTitle($title)
    {
        $this->getView()->setTitle($title);
        return $this;

    }
    
    /**
     * Устанавливает Layout страницы
     * @param type $name
     * @return \Classes\Controller
     */
    public function setLayout($name)
    {
        $this->getView()->setLayout($name);
        return $this;
    }        
    
    /**
     * 
     * @param MenuCollection $menuCollection
     * @return \Classes\Controller
     */
    public function setRights(MenuItem $menuItem)
    {		
            $this->rights = $menuItem;
            return $this;
    }	    

    /**
     * Устанавливает сервис контейнер
     * @param \ServiceContainer $serviceContainer
     */
    public function _setServiceContainer(\ServiceContainer $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }
    
    /**
     * 
     * Устанавливает сообщение об ошибке для следующей страницы
     * После закгрузки следующей страницы, сообщение удаляется из памяти
     * 
     * @param string $alert
     */
    public function setDanger($alert)
    {        
        \App::setDanger($alert);
    }
    
    /**
     * 
     * Устанавливает сообщение для следующей страницы
     * После закгрузки следующей страницы, сообщение удаляется из памяти
     * 
     * @param string $message
     */
    public function setMessage($message)
    {        
        \App::setMessage($message);        
    }
    
    /**
     * 
     * Устанавливает сообщение типа WARNING для следующей страницы
     * После закгрузки следующей страницы, сообщение удаляется из памяти
     * 
     * @param string $warning
     */
    public function setWarning($warning)
    {        
        \App::setWarning($warning);        
    }                              
	
    /**
     * Проверяет права На редактирование
     * @param string $optionName Option name
     * @return boolean
     */
    protected function checkRightW($optionName='')
    {
            if(null === $this->rights){
                    return false;
            }

            if($optionName){
                    $option = $this->getOptionByName($optionName);
                    return null !== $option && $option->getRight() == 'W';
            }

            return strtoupper($this->rights->getRight()) == 'W';
    }

    protected function checkRightR($optionName='')
    {
            if(null === $this->rights){
                    return false;
            }

            if($optionName){
                    $option = $this->getOptionByName($optionName);
                    return null !== $option && in_array(strtoupper($option->getRight()),['R','W']);
            }

            return in_array(strtoupper($this->rights->getRight()), ['R','W']);
    }


    protected function getRightAction($controller, $action)
    {
            $nowRouter = $this->getService('router');
            $router = new Router();
            $router->createFromParams($nowRouter->getBundle(), $controller, $action);
            $security = $this->getService('security');
            return $security->checkAccess($router);
    }
    /**
     * 
     * @return \Services\View
     */
    public function renderNotFound()
    {
            $notFoundView = \App::getNotFoundView();
            $notFoundView->setLayout($this->View->getLayout());
            return $this->View = $notFoundView;
    }

    /**
    * Возвращает  HTML темплейта
    * @param string $template Название темплейта
    * @param array $params параметры
    * @return \Services\View
    */
    public function render($template, $params=array())
    {
        return $this->getView()->render($template, $params);
    }
    
    /**
     * 
     * @param string $text
     * @return \Services\View
     */
    public function renderText($text)
    {
            return $this->getView()->setContentHTML($text);
    }
	
    /**
     * Производит редирект по указанным Контроллеру и акшену     
     * 
     * @param string $controller 
     * @param string $action 
     */
    public function redirect($controller, $action, $params=array())
    {                                
        $this->get('router')->redirect($controller, $action, $params);
    }

    public function redirectPost($controller, $action, $params=array())
    {
        $this->get('router')->redirectPost($controller, $action, $params);
    }
    
    /**
     * Возвращает результат вызова другого контроллера
     * @param string $controller Название контроллера
     * @param string $action  Название метода
     * @param array $params  Массив параметров
     * @param MenuItem $right Вызвать с определенным правом
     * @return string HTML код 
     */
    public function callController($controller, $action, $params=[], MenuItem $right=null)
    {		
        return \App::insert($controller, $action, $params, $right);
    }
	
    public function renderAccessDenied()
    {
            return $this->callController('Index', 'accessDenied');
    }
    
    /**
     * Проверяет на соответствие Метод запроса
     * @param string $method (GET, POST, AJAX)
     * @return boolean
     */
    public function checkRequestMethod($method)
    {                
        $method = strtoupper($method);
		if('AJAX' == $method){
			return $this->get('router')->getRequest()->isAjax();
		}else{
			$RouterRM = strtoupper($this->get('router')->getRequest()->getRequestMethod());
			return $RouterRM == $method;
		}        
	}
	
	
	private function _initNames(): self
	{
		
		$names = explode('\\', $this->className);
		$this->bundleName = $names[0];
		$this->controllerName = str_replace(Router::controllerPostfix, '', array_pop($names));
		
		return $this;
	}		
	
	final public function getClassName(): string
	{
		
		return $this->className;
	}	
	
	final protected function getServiceContainer(): ServiceContainer
	{
		
		return $this->serviceContainer;
	}
		
	final protected function getUri(): Uri
	{
		return $this->uri;
	}
	
	/**
     * Возвращает объект сервиса по имени
     * @param string $name Имя сервиса
     * @return object Объект сервиса
	 * 
	 */
    final protected function getService($name)
    {
		
        return $this->serviceContainer->get($name);
    }
	
	final protected function getControllerName(): string
	{
		
		return $this->controllerName;
	}				
	
	final protected function getBundleName(): string
	{
		return $this->bundleName;
	}
	
	final protected function getFirewall(): Firewall
	{
		
		return $this->getService('firewall');
	}
	
	
	final protected function _getPathParts(): array
	{
		return array_slice($this->uri->getPathParts(), 2);
	}
	
}
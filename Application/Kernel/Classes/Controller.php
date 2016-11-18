<?php
namespace Kernel\Classes;
/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 * @category MED CRM
 */

use Services\Interfaces\ViewInterface;
use Services\Router;
use Kernel\Interfaces\ControllerInterface;


/**
 * Основной класс контроллера
 *
 * 
 */
abstract class Controller implements ControllerInterface {     
    
	public $title;
	
    private $serviceContainer=null;    
    private $View= null;    	            
	private $rights = null;
        
    
    public function __construct(ViewInterface $view) {
        $this->View = $view;
    }
    
    /**
     * Возвращает объект сервиса по имени
     * @param string $name Имя сервиса
     * @return object Объект сервиса
	 * @deprecated
     */
    public function get($name)
    {
        return $this->serviceContainer->get($name);
    }
	
	/**
     * Возвращает объект сервиса по имени
     * @param string $name Имя сервиса
     * @return object Объект сервиса
	 * 
	 */
    public function getService($name)
    {
        return $this->serviceContainer->get($name);
    }
	
    /**
     * Возвращает объект PDO
     * @param string $db
     * @return \Services\DB\PDODriver
     */
    public function getPDOFrom($db)
    {
            return $this->getService('db')->getDBConn($db)->getPdo();
    }

    /**
     * Возвращает объект Пользователя ADUser
     * @return \Classes\ADUser
     */
    public function getUser()
    {
            return $this->get('security')->getUser();
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
    * @return \Services\Menu\MenuItem
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
}

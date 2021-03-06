<?php
namespace Kernel\Classes;
/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 * @category MED CRM
 */

//use Kernel\Services\Interfaces\ViewInterface;
use Kernel\Services\HttpFound\Request;
use Kernel\Services\{Router, HttpFound\Uri, DB\PDODriver};
use Kernel\Services\Firewall\Firewall;
use Kernel\Interfaces\{ControllerInterface, ViewInterface};
use Kernel\Services\Security\Interfaces\UserInterface;
use Psr\Http\Message\RequestInterface;
use ServiceContainer;



/**
 * Основной класс контроллера
 *
 * 
 */
abstract class Controller implements ControllerInterface {     
    
	const controllerPostfix       = 'Controller';
	const defaultControllerName   = 'Index';     
	const defaultActionName       = 'index';
	const actionPostfix           = 'Action';
	const actionControllerPostfix = 'ActionController';
		
	private static $rights = [];
	
	/** @var string */
	public $title;
	
	/** @var string */
	private $className = '';
	
	/** @var string */
	private $bundle;
	
	/** @var string */
	private $controllerName;
	
	private $serviceContainer = null;
	
	/** @var SwRequestInterface*/
	private $request = null;
	
	/** @var ViewInterface */
	private $viewer = null;
    
	
	
    public function __construct(ServiceContainer $serviceContainer, RequestInterface $request)
	{
		$this->request = $request;
        $this->className = get_class($this);		
		$this->serviceContainer = $serviceContainer;		
		$this->viewer = $this->getViewer();
		
		$parts = explode('\\', $this->className);
		$this->bundle = $parts[0];
		$this->controllerName = str_replace(self::controllerPostfix, '', array_pop($parts));
		
		$this->initViewerPathTemplate();
    }		   	
	
	public function initViewerPathTemplate(): self
	{		
		
		$templatesPath = $this->getTemplatesPath();
		
		if(file_exists($templatesPath)){
			$this->viewer->addTemplatePath($templatesPath, $this->getViewNamespace());
		}
		return $this;
		
	}
	
	/** 
	 * return path to templates of this Controller
	 */
	public function getTemplatesPath(): string
	{
		return $this->getFirewall()->getBundlesPath().'/'.$this->bundle
		.'/views/'.$this->controllerName;
	}
	
	/**
	 * return namespace of views of this Controller
	 */
	public function getViewNamespace(): string
	{
		return $this->className;
	}
	
	
	public function getViewer(): ViewInterface
	{
		return $this->serviceContainer->viewer;
	}
	
    /**
     * Возвращает объект PDO
     */
    final protected function getPDO(string $db): PDODriver
    {
		return $this->getService('database')->getDBConn($db)->getPdo();
    }

	/**
	 * Возвращает линк соединения с БД
	 */
	final protected function getConn(string $db)
    {
		return $this->getService('database')->getDBConn($db)->getLink();
    }
	
	
    /**
     * Возвращает объект авторизовавшегося Пользователя
     * @return UserInterface
     */
    final protected function getUser(): UserInterface
    {
		return $this->getFirewall()->getSecurity()->getUser();
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
     * @param MenuCollection $menuCollection
     * @return \Classes\Controller
     */
    public function setRights(MenuItem $menuItem): self
    {		
            $this->rights = $menuItem;
            return $this;
	}   
    
    /**
     * 
     * Устанавливает сообщение об ошибке для следующей страницы
     * После закгрузки следующей страницы, сообщение удаляется из памяти
     * 
     * @param string $alert
     */
    public function setDanger($alert): self
    {        
        \App::setDanger($alert);
		return $this;
    }
    
    /**
     * 
     * Устанавливает сообщение для следующей страницы
     * После закгрузки следующей страницы, сообщение удаляется из памяти
     * 
     * @param string $message
     */
    public function setMessage($message): self
    {        
        \App::setMessage($message);
		return $this;
    }
    
    /**
     * 
     * Устанавливает сообщение типа WARNING для следующей страницы
     * После закгрузки следующей страницы, сообщение удаляется из памяти
     * 
     * @param string $warning
     */
    public function setWarning($warning): self
    {        
        \App::setWarning($warning);  
		return $this;
    }                              
	
    /**
     * Проверяет права На редактирование
     * @param string $optionName Option name
     * @return boolean
     */
    protected function checkRightW($optionName=''): bool
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

    protected function checkRightR($optionName=''): bool
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
    * Возвращает  HTML темплейта
    * @param string $template Название темплейта
    * @param array $params параметры
    * @return \Services\View
    */
    public function render(string $template, &$params=[]):string
    {
		if($this->request->isAjax()){
			$templateFile = $this->getTemplatesPath().'/'.$template.$this->getViewer()->getFileExtension();
			if(!file_exists($templateFile)){
				throw new \FileNotFoundException("Не найден template $template контроллера {$this->getClassName()}");
			}
			
			$string = '';
			$file = file_get_contents($templateFile);
			if(preg_match('/{%\s+block\s+content\s+%}(.*?){%\s+endblock\s+%}/uis', $file, $matches)){
				$string = $matches[1];
			}
			
			$template = $this->getViewer()->createTemplate($string);
			return $template->render($params);
		}
		
        return $this->getViewer()->render($template, $params, $this->getViewNamespace());
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

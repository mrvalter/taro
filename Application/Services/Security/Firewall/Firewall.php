<?php
namespace Services;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


/**
 * Description of Firewall
 *
 * @author sworion
 */
class Firewall {
	
	public function __construct()
	{
		
	}
	
	/**
	 * Проверяет права на просмотр
	 * @param RequestInterface $request
	 * @param type $user
	 * @return boolean
	 */
	public function askShowPermission(RequestInterface $request, $user)
	{
		
	}
		
	/**
	 *  Проверяет права на действия (update, delete, ...custom)
	 * @param RequestInterface $request
	 * @param type $user
	 * @return boolean
	 */
	public function askSpecialPermisson(RequestInterface $request, $user)
	{
		
	}
	
	/**
	 * Проверяет возвращаемые данные
	 * @param ResponseInterface $responce
	 * @return ResponseInterface
	 */
	public function getResponce(ResponseInterface $responce)
	{
		return $responce;
	}
}

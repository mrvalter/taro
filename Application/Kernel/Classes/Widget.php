<?php
namespace Kernel\Classes;
use Kernel\Interfaces\ViewInterface as ViewInterface;

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */



abstract class Widgets extends Controller {
	
	public function __construct(ViewInterface $view)
	{
		
		parent::__construct($view);
	}
}

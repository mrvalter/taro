<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services;

include_once dirname(__FILE__).'/PHPExcel/PHPExcel.php';
include_once dirname(__FILE__).'/PHPExcel/PHPExcel/IOFactory.php';

/**
 * @category MED CRM
 */
class PHPExcel extends \PHPExcel{

	public function loadIOFactory($file_name)
	{
		return \PHPExcel_IOFactory::load($file_name);
	}
}


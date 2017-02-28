<?php

namespace Game_Bundle\Classes\Items;
/**
 * 
 * @property-read integer $id
 * @property-read integer $parentId
 * @property-read integer $level
 * @property-read integer $putOnLevel
 * @property-read integer $weight
 * @property-read integer $crushProtection
 * @property-read integer $cutCoefficient
 * @property-read integer $cutCoefficient
 * @property-read integer $prickCoefficient
 * @property-read integer $prickProtection
 * @property-read integer $shootCoefficient
 * @property-read integer $shootProtection
 * @property-read integer $health
 * @property-read integer $maxHealth
 * @property-read string  $name
 * @property-read string  $description
 * @property-read string  $creatorName
 * @property-read         $images
 * @property-read         $places
 * @property-read         $increaseModifiers
 * @property-read         $activeModifiers
 * @property-read         $material
 * @property-read         $creator
 * 
 */

class Item {
	use \Kernel\Classes\Getter;
		
	private $id = null;	
	private $parentId = null;	
	
	private $level      = 0;
	private $putOnLevel = 0;			
	private $weight = 0;
	private $crushProtection = 0;
	
	/* коэффициент рубки */
	private $cutCoefficient = 0;
	private $cutProtection  = 0;
	
	/* коэффициент укола */
	private $prickCoefficient = 0;
	private $prickProtection  = 0;
	
	/* коэффициент стрельбы */
	private $shootCoefficient = 0;
	private $shootProtection  = 0;
	
	/* Прочность */
	private $health    = 1;
	private $maxHealth = 1;
	
	private $name        = '';
	private $description = '';
	private $descriptionFull = '';
	private $creatorName = '';
	
	private $images = null;			
	private $places = null;		
	private $material = null;
	private $creator = null;
	private $increaseModifiers = null;
	private $activeModifiers   = null;

	public function __construct(\PDORow $params = null)
	{
		$this->id                = $params->id ?? null;
		$this->parentId          = $params->parentId ?? null;
		$this->level             = $params->level ?? 1;
		$this->putOnLevel        = $params->putOnLevel?? 0;
		$this->weight            = $params->weight ?? 0;
		$this->crushProtection   = $params->crushProtection ?? 0;	
		$this->cutCoefficient    = $params->cutCoefficient ?? 0;
		$this->cutProtection     = $params->cutProtection ?? 0;		
		$this->prickCoefficient  = $params->prickCoefficient ?? 0;
		$this->prickProtection   = $params->prickProtection ?? 0;		
		$this->shootCoefficient  = $params->shootCoefficient ?? 0;
		$this->shootProtection   = $params->shootProtection ?? 0;	
		$this->health            = $params->health ?? 0;
		$this->maxHealth         = $params->maxHealth ?? 0;
		
		$this->name              = isset($params->name)? trim($params->name) : '';
		$this->description       = isset($params->description)? trim($params->description) : '';
		$this->descriptionFull   = isset($params->descriptionFull)? trim($params->descriptionFull) : '';
		$this->creatorName       = isset($params->creatorName)? trim($params->creatorName) : '';
		
		$this->images            = null;			
		$this->places            = null;		
		$this->material          = null;
		$this->creator           = null;
		$this->increaseModifiers = null;
		$this->activeModifiers   = null;
		
	}
	
	public function setIncreaseModifiers()
	{
		
	}
	
	public function setActiveModifiers()
	{
		
	}
	
}

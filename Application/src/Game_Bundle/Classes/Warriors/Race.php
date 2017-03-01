<?php
namespace Game_Bundle\Classes\Warriors;

/**
 * @property-read integer  $id
 * @property-read string   $name 
 * @property-read integer  $strength
 * @property-read integer  $agility
 * @property-read integer  $health
 * @property-read integer  $wisdom
 * @property-read integer  $mana
 * @property-read integer  $anger
 * @property-read Image    $mainImage
 * 
 * @property-read ObjectsCollection  $slots	
 * @property-read ObjectsCollection  $magicDefence
 * @property-read ObjectsCollection  $passiveSkills
 * @property-read ObjectsCollection  $activeSkills
 */

use Kernel\Classes\Types\ObjectsCollection;

class Race {
		
	private $id;
	private $name;		
	private $strength;	
	private $wisdom;
	private $health;		
	private $mana;
	private $agility;
	private $anger;		
	private $slots;
	private $magicDefence;
	private $passiveSkills;
	private $activeSkills;
	private $images;
	
	public function __construct(
		$rows, 
		ObjectsCollection $slots,
		ObjectsCollection $magicDefence     = null,
		ObjectsCollection $passiveSkills    = null,
		ObjectsCollection $activeSkills     = null,	
		ObjectsCollection $images           = null		
		
	) {
		
		$this->id       = $rows->id ?? null;
		$this->name     = $rows->name ?? '';
		$this->sex      = $rows->sex ?? '';		
		$this->strength = $rows->strength ?? 0;
		$this->agility  = $rows->agility ?? 0;
		$this->health   = $rows->health ?? 0;
		$this->mana     = $rows->mana ?? 0;
		$this->anger    = $rows->anger ?? 0;
		$this->wisdom   = $rows->wisdom ?? 0;
		
		$this->slots           = $slots           ?? new ObjectsCollection();
		$this->passiveSkills   = $passiveSkills   ?? new ObjectsCollection();
		$this->activeSkills    = $activeSkills    ?? new ObjectsCollection();
		$this->magicDefence    = $magicDefence    ?? new ObjectsCollection();
		
	}
}

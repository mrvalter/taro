<?php

namespace Game_Bundle\Classes\Warriors;

/**
 * @property-read integer  $id
 * @property-read string  $name
 * @property-read integer  $sex
 * @property-read Race     $race
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
 * @property-read ObjectsCollection  $decrasedEffects
 * @property-read ObjectsCollection  $incrasedEffects
 * @property-read ObjectsCollection  $passiveSkills
 * @property-read ObjectsCollection  $activeSkills
 * 
 */
use Game_Bundle\Classes\Items\Item;
use Game_Bundle\Exceptions\ActionProhibitedException;
use Kernel\Classes\Types\ObjectsCollection;
use Kernel\Classes\Types\Image;

abstract class Warrior {
	use \Kernel\Classes\Getter;
	
	protected $id;
	protected $name;
	protected $level;
	protected $sex;
	protected $race;		
	protected $strength;	
	protected $agility;
	protected $health;
	/* мудрость */
	protected $wisdom;
	
	protected $mana;
	protected $anger;		
	protected $mainImage;
	
	protected $slots;	
	protected $magicDefence;	
	protected $decrasedEffects;
	protected $incrasedEffects;	
	protected $passiveSkills;
	protected $activeSkills;
	
	public function __construct(
		$rows,
		Race $race = null,		
		ObjectsCollection $slots = null,		
		ObjectsCollection $magicDefence = null,
		ObjectsCollection $decrasedEffects = null,
		ObjectsCollection $incrasedEffects  = null,
		ObjectsCollection $passiveSkills    = null,
		ObjectsCollection $activeSkills     = null
	)
	{
		$this->id       = $rows->id ?? null;
		$this->name     = $rows->name ?? '';
		$this->sex      = $rows->sex ?? '';		
		$this->strength = $rows->strength ?? 0;
		$this->agility  = $rows->agility ?? 0;
		$this->health   = $rows->health ?? 0;
		$this->mana     = $rows->mana ?? 0;
		$this->anger    = $rows->anger ?? 0;
		$this->wisdom   = $rows->wisdom ?? 0;
		$this->level    = $rows->level  ?? 1;
		
		$this->race            = $race            ?? new Race();
		$this->slots           = $slots           ?? new ObjectsCollection();		
		$this->magicDefence    = $magicDefence    ?? new ObjectsCollection();
		$this->decrasedEffects = $decrasedEffects ?? new ObjectsCollection();
		$this->incrasedEffects = $incrasedEffects ?? new ObjectsCollection();		
		$this->passiveSkills   = $passiveSkills   ?? new ObjectsCollection();
		$this->activeSkills    = $activeSkills    ?? new ObjectsCollection();
		
	}
	
	public function putOnItem(int $slotId, Item $item)
	{
		$slot = $this->slots->getById($slotId);
		if(!$slot->isExists()) {
			throw new ActionProhibitedException('item_not_fit_to_slot');
		}
		
		/* Проверяем есть ли у нас эта вещь */
		$item = $this->bag->getById($item->id);
		if(null !== $item){
			$this->bag->removeById($item->id);			
		}
				
	}		
	
	public function setIncreasedEffects()
	{
		
	}
	public function addIncreasedEffect()
	{
		
	}
	public function setDecrasedEffects()
	{
		
	}
	public function addDecreasedEffect()
	{
		
	}
}

<?php

namespace Game_Bundle\Classes\Mobs;

use Game_Bundle\Classes\Items\Item;
use Game_Bundle\Exceptions\ActionProhibitedException;

abstract class Warrior {
	
	private $id;
	private $name;
	private $sex;		
	
	private $strength;	
	private $agility;
	private $health;
	private $mana;
	private $anger;
	
	private $slots;
	private $bag;
	
	private $decrasedEffects;
	private $increasedEffects;
	
	public function __construct($rows)
	{
		
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
	
	public function putInBagItem(int $bagSlotId, Item $item)
	{
		
	}
	
	abstract public function setIncreasedEffects();	
	abstract public function addIncreasedEffect();	
	abstract public function setDecrasedEffects();	
	abstract public function addDecreasedEffect();		
}

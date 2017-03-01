<?php

namespace Game_Bundle\Classes\Warriors;

use Game_Bundle\Classes\Items\Item;
use Game_Bundle\Exceptions\ActionProhibitedException;
use Kernel\Classes\Types\ObjectsCollection;
use Kernel\Classes\Types\Image;

/**
 * Description of Npc
 *
 * @author sworion
 */
class Npc extends Warrior{
	
	private $quests;
	
	public function __construct(
		$rows, 
		Race $race = null, 
		ObjectsCollection $slots = null, 
		ObjectsCollection $magicDefence = null, 
		ObjectsCollection $decrasedEffects = null, 
		ObjectsCollection $incrasedEffects = null, 
		ObjectsCollection $passiveSkills = null, 
		ObjectsCollection $activeSkills = null,
		ObjectsCollection $quests = null
	) {
		
		parent::__construct($rows, $race, $slots, $magicDefence, $decrasedEffects, $incrasedEffects, $passiveSkills, $activeSkills);
		$this->quests = $quests ?? new ObjectsCollection();
	}
}

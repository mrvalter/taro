<?php

namespace Game_Bundle\Classes\Warriors;


use Game_Bundle\Exceptions\ActionProhibitedException;
use Kernel\Classes\Types\ObjectsCollection;
use Kernel\Classes\Types\Image;

class Unit extends Warrior {
	
	use \Kernel\Classes\Getter;		
	
	private $experience;
	
	public function __construct($rows,
		Race $race = null, 		
		ObjectsCollection $slots           = null,
		ObjectsCollection $magicDefence    = null,
		ObjectsCollection $decrasedEffects = null,
		ObjectsCollection $incrasedEffects = null
	) {
		
		parent::__construct($rows, $race, $slots, $bag, $magicDefence, $decrasedEffects, $incrasedEffects);
		
		$this->experience = $rows->experience ?? 0;
		
	}
	
	
}
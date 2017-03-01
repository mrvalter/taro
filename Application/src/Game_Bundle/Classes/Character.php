<?php
namespace Game_Bundle\Classes;

use Game_Bundle\Classes\Warriors\Unit;
use Kernel\Classes\Types\ObjectsCollection;

class Character {	
	use \Kernel\Classes\Getter;
	
	private $id;
	private $allEarnedMoney;
	private $allEarnedGold;		
	private $money;
	private $gold;
	
	private $email;
	private $mobilePhone;
	private $birthday;
	private $sex;
	private $city;
	private $country;
	private $comments;	
	private $password;
	private $name;
	private $sername;
	private $patronymic;
	
	private $bag;
	private $resourses;
	private $units;
	private $socials;
	
	private $questsActive;
	private $questsFinished;
	
	
	public function __construct(
		$rows, 		
		ObjectsCollection $bag             = null,
		ObjectsCollection $resourses       = null,		
		ObjectsCollection $units           = null,
		ObjectsCollection $questsActive    = null,
		ObjectsCollection $questsFinished  = null
		
		) {
		
		$this->id             = $rows->id             ?? null;
		$this->money          = $rows->money          ?? 0;
		$this->gold           = $rows->gold           ?? 0;
		$this->allEarnedGold  = $rows->allEarnedGold  ?? 0;
		$this->allEarnedMoney = $rows->allEarnedMoney ?? 0;
		$this->email          = $rows->email          ?? '';
		$this->mobilePhone    = $rows->mobilePhone    ?? '';
		$this->birthday       = $rows->birthday       ?? '';
		$this->sex            = $rows->sex            ?? null;
		$this->city           = $rows->city           ?? '';
		$this->country        = $rows->country        ?? '';
		$this->comments       = $rows->comments       ?? '';		
		$this->password       = $rows->password       ?? null;
		$this->name           = $rows->name           ?? '';
		$this->sername        = $rows->sername        ?? '';
		$this->patronymic     = $rows->patronymic     ?? '';
				
		$this->bag              = $bag            ?? new ObjectsCollection();
		$this->resourses        = $resourses      ?? new ObjectsCollection();
		$this->units            = $units          ?? new ObjectsCollection();
		$this->questsActive     = $questsActive   ?? new ObjectsCollection();
		$this->questsFinished   = $questsFinished ?? new ObjectsCollection();
	}
	
	public function setMoney(int $money=0): self
	{
		
		$this->money = $money < 0 ? 0 : $money;
		return $this;
	}
	
	public function setGold(int $gold=0): self
	{
		
		$this->money = $gold < 0 ? 0 : $gold;
		return $this;
	}		
	
	public function addNewUnit(Unit $unit)
	{		
		
		if($unit->isExists()){
			throw new ActionProhibitedException('add_existing_unit_to_character');
		}
		
		$this->units->push($unit);
		
	}
	
	public function addMoney(int $money=0): self
	{
		
		$this->money += $money;
		
		if($money > 0){
			$this->allEarnedMoney += $money;
		}
		
		return $this;
	}
	
	public function addGold($gold): self
	{
		
		$this->gold += $gold;
		
		if($gold > 0){
			$this->allEarnedGold += $gold;
		}
		
		return $this;
	}
	
	public function putInBagItem(int $bagSlotId, Item $item)
	{
		
	}
}

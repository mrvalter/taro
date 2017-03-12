<?php
namespace Game_Bundle\Classes;


class TestRepository extends \Kernel\Classes\Repository{
	
	
	public function testConnectMongoDB()
	{		
		$this->testWrite();
		$mongo = $this->getConnFrom('mongodb');
		
		$query = new \MongoDB\Driver\Query([]);		
		$rows = $mongo->executeQuery('game.users', $query);
		
		var_dump($rows);
		foreach($rows as $document){
			var_dump($document);
		}
		
		
		die();
	}
	
	public function testWrite()
	{
		
		$mongo = $this->getConnFrom('mongodb');
		$bulk = new \MongoDB\Driver\BulkWrite();
		$bulk->insert([
			"_id" => new \MongoDB\BSON\ObjectID(),
			"name"=>'Alex',
			"login"=>"Alexlogin6",
			"password"=>"Password",
			"items"=>[
				[
					'old_id'=>3456,
					'name'=>'knight'
				],
				[
					'old_id'=>3457,
					'name'=>'knight2'
				]				
			],
			
			"items2"=>[
				[
					'old_id'=>3456,
					'name'=>'knight'
				],
			]
		]);
		
		$writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 100);
		$mongo->executeBulkWrite('game.users', $bulk, $writeConcern);
		
	}
	
	public function testUpdate()
	{
		
		$mongo = $this->getConnFrom('mongodb');
		$bulk = new \MongoDB\Driver\BulkWrite();
		$bulk->update(
			["_id" => new \MongoDB\BSON\ObjectID('58c49c701266633683333661'), 'items.old_id'=>3456],
			[
			'$set'=> ['items.name'=>'vilka09', 'items.strength'=>3000]				
			]);
		
		$writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 100);
		$mongo->executeBulkWrite('game.users', $bulk, $writeConcern);
	}
}

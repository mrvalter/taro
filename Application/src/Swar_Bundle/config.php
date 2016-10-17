<?php

return [
'services' => [
		'db'=>[
			'class'=> 'Services\DB',
			'params'=>[
				'dbases'=> [
					/** сервис работы с базой данных Office */
					'dbOffice' =>  [
						'host'     => 'eoffice2.moscow22222',
						'user'     => 'WEB_USER',
						'password' => '95175385244444',
						'encoding' => 'UTF8',
						'dbname'   => 'E-Office_dbo'
					],
				]
			]
		]
	]
];
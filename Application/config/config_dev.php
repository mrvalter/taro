<?php
return [
	
    'media_path'        => '/Application/src/%bundle%/media',  /* Путь к папке медиа проекта */
    'global_media_path' => '/Application/src/%public%/media',  /* Путь к глобальной папке медиа */
    'cache_dir'         => '/cache',	
    
     'services' => [        		
		'logger' => [
			'class' =>'Services\Logger',
			'params'=> [												
				'dblog' => 0,
				'systemlog' => 1,
				'long_query_time' => 0
			]
		],
    ],		
];
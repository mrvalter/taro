<?php

/**
 *  %bundle% - имя бандла
 *  %public% - имя публичного бандла   
 *  %bundlePath% - путь от корня до Бандла
 */

return array(    	
	
	'media_path'        => '/%bundle%/Application/src/%bundle%/media',  /* Путь к папке медиа проекта */
	'global_media_path' => '/%bundle%/Application/src/%public%/media',  /* Путь к глобальной папке медиа */
	'cache_dir'         => '/MatMedV2_cache',	
	
	'user_image_path'   => [
            'moscow' => '/mnt/E-Office/usersphoto/Moscow',
            'chel'   => '/mnt/E-Office/usersphoto/Chel',
	],
    
    'admin_mails' => [
        'fedyakinas@master'
    ],
		
    /** 
     * Сервисы 
     * Конфигурационный сервис config, зарезервирован по умолчанию
     * config class = Services\Config
     */
    'services' => [
        
        'config'  => 'RESERVED',        
        'router'  => 'RESERVED',
        'menu'    => 'RESERVED',
	
        'user_repository' => [
            'class' => 'Classes\\UserRepository'
        ],

        'authenticator'   => [
            'class'  => 'Services\\Security\\Authentication\\DbAuthenticator',
            'params' => ['@user_repository']
        ],
        
        /*'authenticator'   => [
            'class'  => 'Services\\Security\\Authentication\\LdapAuthenticator',
            'params' => ['@user_repository', '@ldap']
        ],*/
        
        'session_storage'=>[
            'class' =>'Services\Security\SessionStorage\NativeSessionStorage',
        ],               
        
        'db'=>[
            'class'=> 'Services\DB',
            'params'=>[
                'dbases'=> [
                    /** сервис работы с базой данных Office */
                    'dbOffice' =>  [
                            'host'     => 'eoffice2.moscow',
                            'user'     => 'USER',
                            'password' => '52',
                            'encoding' => 'UTF8',
                            'dbname'   => 'E-Office_dbo'
                    ],

                    'dbOfficeRoot' =>  [
                            'host'     => 'mysqlsrv',
                            'user'     => 'SER',
                            'password' => '52',
                            'encoding' => 'UTF8',
                            'dbname'   => 'E-Office_dbo'
                    ],					

                    'dbOfficeMSSQL' =>  [
                            'driver'     => 'dblib',
                            'host'     => 'lord.moscow',
                            'user'     => 'ffice',
                            'password' => '3',
                            'encoding' => 'cp1251',
                            'dbname'   => 'E-Office'
                    ],
                ],
            ],
        ],				
		
        'mailer' => [
                'class' =>'Services\Mailer',
                'params'=> [
                        'config' => [
                                'from_email' => 'admin@zmail.da.ru',
                                'from_name'  => 'Mail Delivery System',
                                'language'   => 'ru',
                                'type'       => 'sendmail',					
                        ],

                        'env' => getenv('APP_ENV')

                ]
        ],

        'phpexcel' => [
                'class' =>'Services\PHPExcel',
                'params'=> []
        ],

        'domtopdf' => [
                'class' =>'Services\DomToPdf',
                'params'=> []
        ],

        'events' => [
                'class' =>'Services\Events',
                'params'=> [												
                    'db'=>'@db',
                    'config'=>'@_config',
                    'mailer'=>'@mailer',
                    'security'=>'@security'                
                ]
        ],

        'logger' => [
                'class' =>'Services\Logger',
                'params'=> [												
                    'dblog' => 0,
                    'systemlog' => 0,
                    'long_query_time'=>0				
                ]
        ],
		
    ],   
	
	'project_descr' => [
		'titleEO' => 'E-Office',
		'pathEO'  => 'https://office.materiamedica.ru/',
		'charset' => 'UTF-8',
		'layout'  => 'default.layout',
	],
	
	'LDAP' => [
		'moscow'=>[
			'user'     => 'ld4',
			'password' => '781',
			'connstr'  => 'moscow',
			'dn'       => 'ели,DC=Moscow'
		]
	]
);

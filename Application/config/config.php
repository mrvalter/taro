<?php

/**
 *  синонимы, работают только на уровне контроллеров
 * 
 *  %bundle% - имя бандла
 *  %public% - имя публичного бандла   
 *  %bundlePath% - путь от корня до Бандла (Запроса действующий запрос)
 *	%AppPath%    - путь от бандла до директории
 */

return [  	
	
    'media_path'        => '/%bundle%/Application/src/%bundle%/media',  /* Путь к папке медиа проекта */
    'global_media_path' => '/%bundle%/Application/src/%public%/media',  /* Путь к глобальной папке медиа */
    'cache_dir'         => '/MatMedV2_cache',	

    'user_image_path'   => [
        'moscow'        => '/mnt/E-Office/usersphoto/Moscow',
        'chel'          => '/mnt/E-Office/usersphoto/Chel',
    ],
    
    'admin_mails' => [
        'fedyakinas@master'
    ],

    /** 
     * Сервисы     
     */
    'services' => [        		
        'config'      => 'Kernel', // сервис конфигурации 		
        'router'      => 'Kernel', // сервис роутинга/		
        'autoloader'  => 'Kernel', // сервис подгрузки классов        			
		
		'session_storage' => [
            'class' =>'Kernel\Services\Security\SessionStorage\NativeSessionStorage',

        ], 
				
		'user_repository' => [
            'class' => 'Classes\UserRepository'
        ],
		
		'authenticator'   => [
            'class'  => 'Kernel\\Services\\Security\\Authentication\\DbAuthenticator',
            'params' => ['@user_repository']
        ],
		
		/*'authenticator'   => [
            'class'  => 'Services\\Security\\Authentication\\LdapAuthenticator',
            'params' => ['@user_repository', '@ldap']
        ],*/	        
        
		'security' => [
            'class' =>'Kernel\Services\Security\Security',
            'params'=>[
                'authenticator'  => '@authenticator',
                'userRepository' => '@user_repository',
                'sessionStorage' => '@session_storage',
                
            ]
        ],		               
        
        'db'=>[
            'class'=> 'Kernel\Services\DB',
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
                    
                ],
            ],
        ],
		
		'viewer' => [
			'class' => 'Kernel\Services\Viewer\TwigViewer',
			'params' => [
				'cachePath'   => '%AppPath%/cache/kernel/twig',
				'layoutsPath' => '%AppPath%/layouts'
				
			]
		]
	]		       
];

?>

<root>
<services>
	<service name="viewer" class="Kernel\Services\Viewer\TwigViewer">
		<params>
			<param name="cachePath" value="%AppPath%/cache/kernel/twig"/>
			<param name="layoutsPath" value="%AppPath%/layouts"/>
		</params>
	</service>
	
	<service name="db" class="Kernel\Services\DB">
		<params>			
			<param></param>
		</params>
	</service>
	
</services>
</root>
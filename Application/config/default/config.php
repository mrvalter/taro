<?php
return  [
	'cache_dir'    => '%App%/cache',
	'admin_mails'  => 'al.fedyakin@gmail.com',
	'masterAdmins' => 'FedyakinAS',
	'media'        => '/media/tl',

	'services'  => [
		#  config: Kernel
		#  router: Kernel
		#  autoloader: Kernel
				
		'session_storage' => [
			'class' => 'Kernel\Services\Security\SessionStorage\NativeSessionStorage',
		],
        
		'user_repository' => [
			'class' => 'Classes\UserRepository',
		],

		'authenticator' =>[
			'class' => 'Kernel\Services\Security\Authentication\DbAuthenticator',
			'params'=> ['@user_repository']
		],

		'authenticatorLdap' => [
			'class' => 'Kernel\Services\Security\Authentication\LdapAuthenticator',
			'params'=> ['@user_repository']
		],
  
		'menu_builder' => [
			'class' => 'Kernel\Services\MenuBuilder_MM\MenuBuilder'
		],
      
		'security' => [
			'class' => 'Kernel\Services\Security\Security',			
			'params' => ['@authenticator', '@user_repository', '@session_storage', '@menu_builder']
		],
  
		'viewer'=> [
			'class' => 'Kernel\Services\Viewer\TwigViewer',
			'params'=>[
				'params' => [
					'cache' => '%App%/cache/kernel/vi',
					'layoutPath'=> '%App%/layouts',
					'layout' => 'default.layout'
				]
			]
		],

		'database' => [
			'class' => 'Kernel\Services\DB',
			'params'=> [
				'connects'=>[
					'db'=> [
						'host'     => 'localhost',
						'user'     =>  'root',
						'password' => 'ghbdtn',
						'encoding' => 'UTF8',
						'driver'   => 'mysql',
						'dbname'   => 'medcrm'
					],
				]
			]
		]
	]
];
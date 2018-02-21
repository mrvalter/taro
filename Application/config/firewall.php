<?php
#http_path_prefix: /MatMedV2_CRM
return [
	'registration' => 'off',

	'public_urls' => [
		'/',
		'/api/public',
		'/main/firewall/authorize',
		'/main/firewall/registration',
		'/game',
		'~^\/share(?=\\/|$)~ui',
		'/widgets'
	],

# Подключаемые бандлы
	'required_bundles' => [
		'firewall' => 'Firewall_Bundle',
		'users'    => 'Users_Bundle',
		'main'     => 'Main_Bundle',
		'widgets'  => 'Widgets_Bundle',
		'game'     => 'Game_Bundle'
	],

#бандл главной страницы (bundle/IndexController->indexAction())
	'main_page_bundle' => 'main',

#бандл виджетов
	'widgets_bundle'  =>  'widgets',

#Назначаем отображение определенных страниц на коды Ответов
# Если будет Ответ содержит код, не прописанный ниже или страницы не существует, 
# вернется стандартный Response  с кодом
	'system_responses' => [
		'401' => '/main/firewall/authorize',
		'403' => '/share/firewall/accessdenied',
		'404' => '/firewall/message/notfound'
	]
	
];

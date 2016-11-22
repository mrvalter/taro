<?php
return [
		
    'public_urls' => [
        '/',                        //главная страница
        //'/share',                 // открыт публичный доступ к бандлу share		
		//'/share/sysinfo',         // открыт публичный доступ к контроллеру sysinfo
		//'/share/sysinfo/phpinfo', // открыт публичный доступ к методу phpinfo контроллера sysinfo
		'/firewall/message',
		'~^\/share(?=\\/|$)~ui'      // открыт публичный доступ к бандлу share	регуляркой
    ],
	
	/* Подключаемые бандлы */
    'required_bundles' => [
        'swar'     => 'Swar_Bundle',
		'share'    => 'Share_Bundle',
        'firewall' => 'Firewall_Bundle',
    ],
        
	/* путь к главной странице */
    'main_page_bundle' => 'share', // Путь до бандла главной страницы
	
	/* 
	 * Назначаем отображение определенных страниц на коды Ответов 
	 * Если будет Ответ содержит код, не прописанный ниже или страницы не существует, 
	 * вернется стандартный Response  с кодом
	 */
	'system_responses'=> [
		'401'     => '/share/firewall/authorize',
		'403'     => '/share/firewall/accessdenied',
		'404'     => '/firewall/message/notfound'
	],
	
	/* 
	 * Если контроллер может динамически возвращать файлы, 
	 * и url вида /share/getfile/pic.png (окончание с расширением файла)
	 * добавляем сюда:  1*
	 */
	'file_download_actions' => [
		'/share/getfile',
	],			    
	
	
	/* 1*: 
	 *		Сделано для увеличения скорости при запросе несуществующего файла
	 * 
	 */
];
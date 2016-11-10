<?php
return [
		
    'public_urls' => [
        '/',                      //главная страница
        //'/share',                 // открыт публичный доступ к бандлу share		
		//'/share/sysinfo',         // открыт публичный доступ к контроллеру sysinfo
		//'/share/sysinfo/phpinfo', // открыт публичный доступ к методу phpinfo контроллера sysinfo
		
		'~^\/share(?=\\/|$)~ui'      // открыт публичный доступ к бандлу share	регуляркой
    ],    
    
    'required_bundles' => [
        'swar'    => 'Swar_Bundle',
		'shared'  => 'Share_Bundle',
        'share'   => 'Share_Bundle',
    ],
    
    
    'main_page_bundle' => 'share', // Путь до бандла главной страницы
    
];
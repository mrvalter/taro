<?php
return [
    
    'public_urls' => [
        '~^/$~', //главная страница
        '~^/share$~',
        '~^/share/~',
    ],    
    
    'required_bundles' => [
        'swar'  => 'Swar_Bundle',
        'share' => 'Share_Bundle',
    ],
    
    
    'main_page_bundle' => 'share', // Путь до банла главной страницы
    
];
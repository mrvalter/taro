<?php

setlocale(LC_ALL, 'ru_RU.utf8');

/*for($x=0; $x<=100; $x=$x+rand(0,10)){	
	echo ("\r\033[01;32m loading: [".str_repeat('#', $x). str_repeat('.', 100-$x).']'."\033[0m");	
	sleep(rand(0,1));		
}
*/

require_once __DIR__.'/Application/App.php';
$loader = require_once __DIR__.'/Application/autoload.php';
App::run(__DIR__, $loader);


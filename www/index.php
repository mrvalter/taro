<?php
//die();
setlocale(LC_ALL, 'ru_RU.utf8');

	
function e($v) {
	echo htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

require_once __DIR__.'/../Application/App.php';
$loader = require_once __DIR__.'/../Application/autoload.php';

App::run(__DIR__, $loader);


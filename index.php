<?php
setlocale(LC_ALL, 'ru_RU.utf8');

require_once __DIR__.'/Application/App.php';
$loader = require_once __DIR__.'/Application/autoload.php';
App::run(__DIR__, $loader);


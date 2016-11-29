<?php
setlocale(LC_ALL, 'ru_RU.utf8');

require_once __DIR__.'/App.php';
$loader = require_once __DIR__.'/autoload.php';
$wwwDir = __DIR__.'/../www';

App::runConsole($wwwDir, $loader, $argv);

<?php
require_once __DIR__.'/ServiceContainer.php';

$loader = require_once __DIR__.'/vendor/autoload.php';
$loader->add('Services\\', __DIR__);
$loader->add('Classes\\', __DIR__);
$loader->add('Interfaces\\', __DIR__);
$loader->add('', __DIR__.'/Exceptions');

$loader->add('Swar_Bundle\\', __DIR__.'/src');



return $loader;

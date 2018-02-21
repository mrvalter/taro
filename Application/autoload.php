<?php

$loader = require_once __DIR__.'/vendor/autoload.php';
$loader->add('Kernel\\', __DIR__);
$loader->add('Services\\', __DIR__);
$loader->add('Classes\\', __DIR__);
$loader->add('Interfaces\\', __DIR__);
$loader->add('', __DIR__.'/Kernel/Exceptions');
$loader->add('', __DIR__.'/Exceptions');

if(file_exists(__DIR__.'/ServiceContainer.php')){
	require_once __DIR__.'/ServiceContainer.php';
}
if(file_exists(__DIR__.'/ServiceContainerD.php')){
	require_once __DIR__.'/ServiceContainerD.php';
}

return $loader;

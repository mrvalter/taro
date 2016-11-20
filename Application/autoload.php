<?php
require_once __DIR__.'/ServiceContainer.php';

$loader = require_once __DIR__.'/vendor/autoload.php';
$loader->add('Kernel\\', __DIR__);
$loader->add('Services\\', __DIR__);
$loader->add('Classes\\', __DIR__);
$loader->add('Interfaces\\', __DIR__);

$loader->add('', __DIR__.'/Kernel/Exceptions');
$loader->add('', __DIR__.'/Exceptions');



/*Twig_Autoloader::register();
$twLoader = new Twig_Loader_Filesystem( __DIR__.'/layouts');
$twig = new Twig_Environment($twLoader, array(
    'cache' => __DIR__.'/cache/Twig',
));*/

return $loader;

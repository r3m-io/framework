<?php

$dir = __DIR__;

$dir_vendor =
dirname($dir, 1) .
DIRECTORY_SEPARATOR .
'vendor' .
DIRECTORY_SEPARATOR;

$autoload = $dir_vendor . 'autoload.php';
$autoload = require $autoload;

$config = new R3m\Io\Config(
    [
        'dir.vendor' => $dir_vendor
    ]
    );

$config->data('framework.environment', R3m\Io\Config::MODE_DEVELOPMENT);

$app = new R3m\Io\App($autoload, $config);
if(method_exists($app, 'preRun')){
	echo R3m\Io\App::preRun($app);
}
echo R3m\Io\App::run($app);
if(method_exists($app, 'postRun')){
	echo R3m\Io\App::postRun($app);
}

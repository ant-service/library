<?php

use AntService\Module;
use AntService\OutPut;
use AntService\Src\Common\Config;

require_once "vendor/autoload.php";

$userDir = getUserDir();
Module::use('a','a');
var_dump($userDir);
// successOutput(['a' => 'aa']);
// Config::read('database');
// $aa = Config::readEnv('CACHE_MODULE');
// var_dump($aa);

<?php

use AntService\Cache;
use AntService\Module;
use AntService\OutPut;
use AntService\Src\Common\Config;
use AntService\Src\DataBase\Depend;

require_once "vendor/autoload.php";

// Depend::syncDataBase([
//     'user' => 'id,nickname,age',
//     'user_account' => 'id,uid,username,password'
// ]);
// $userDir = getUserDir();
// Module::use('a','a');
// var_dump($userDir);
// successOutput(['a' => 'aa']);
// Config::read('database');
// $aa = Config::readEnv('CACHE_MODULE');
// var_dump($aa);
Cache::set('aa','bb',1);

// echo Cache::get('aa');
var_dump(getCache('aa'));
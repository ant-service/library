<?php

namespace AntService\Src\Common;

use AntService\OutPut;

class Config
{
    public static function read(string $configName)
    {
        $keyArray = explode('.', $configName);
        $filePath = $_SERVER['DOCUMENT_ROOT'] . 'config/' . array_shift($keyArray) . '.json';
        $configContent = $GLOBALS['config_' . $filePath] ?? null;
        if (!is_file($filePath)) exit('读取配置文件失败,请检查配置文件[' . $filePath . ']是否存在');
        if ($configContent === null) {
            $configContent = DataType::convertArray(file_get_contents($filePath));
            $GLOBALS['config_' . $filePath] = $configContent;
        }
        foreach ($keyArray as $key) {
            $configContent = $configContent[$key];
        }
        return $configContent;
    }

    public static function write(string $configName, $content)
    {
        $keyArray = explode('.', $configName);
        $filePath =  $_SERVER['DOCUMENT_ROOT'] .  'config/' . array_shift($keyArray) . '.json';
        $contentArr = array();
        if (is_file($filePath)) {
            $contentArr = DataType::convertArray(file_get_contents($filePath));
        }
        $keyArray = array_reverse($keyArray);
        $lastKey = '';
        $newContent = array();
        foreach ($keyArray as $key) {
            $newContent[$key] = $content;
            unset($newContent[$lastKey]);
            $content = $newContent;
            $lastKey = $key;
        }
        if (!count($keyArray)) {
            $contentArr = array();
        }
        file_put_contents($filePath, json_encode(array_merge($contentArr, $content), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    public static function readEnv(string $configName)
    {
        $keyArray = explode('.', $configName);
        $envContent = $GLOBALS['env_content'];
        if ($envContent === null) {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . '.env';
            if (!is_file($filePath)) exit('读取配置文件失败,请检查配置文件[' . $filePath . ']是否存在');
            $envContent = parse_ini_string(str_replace('#', ';', file_get_contents($filePath)));
            $GLOBALS['env_content'] = $envContent;
        }
        foreach ($keyArray as $key) {
            $envContent = $envContent[$key] ?? null;
        }
        return $envContent;
    }
}

<?php

namespace AntService\Src\Common;

use AntService\OutPut;

class Config
{
    public static function read(string $configName)
    {
        var_dump($_SERVER['DOCUMENT_ROOT']);exit();
        $keyArray = explode('.', $configName);
        $filePath = $_SERVER['DOCUMENT_ROOT'] . 'config/' . array_shift($keyArray) . '.json';
        $configContent = $GLOBALS['config_' . $filePath] ?? null;
        if (!is_file($filePath)) {
            return OutPut::error('READ_CONFIG_FAIL', '读取配置文件失败,请检查配置文件[' . $filePath . ']是否存在');
        }
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
}

<?php

namespace AntService\Src\Cache;

use AntService\Src\Interfaces\Cache;

class File implements Cache
{
    public static function get(string $key)
    {
        $filePath = self::getCacheFilePath($key, $keyName);
        $cacheData = unserialize(base64_decode(file_get_contents($filePath)));
        if ($cacheData === false) {
            return null;
        }
        if (($cacheData[$keyName]['expires'] ?? 0) - time() < 0) {
            unset($cacheData[$keyName]);
            file_put_contents($filePath, base64_encode(serialize($cacheData)));
        }
        return $cacheData[$keyName]['value'] ?? null;
    }

    public static function set(string $key, $value, int $expires = 7200)
    {
        $filePath = self::getCacheFilePath($key, $keyName);
        $cacheData = unserialize(base64_decode(file_get_contents($filePath)));
        if ($cacheData === false) {
            $cacheData = array();
        }
        $cacheData[$keyName] = array('value' => $value, 'expires' => $expires + time());
        return (bool) file_put_contents($filePath, base64_encode(serialize($cacheData)));
    }

    public static function remove(string $key)
    {
        $filePath = self::getCacheFilePath($key, $keyName);
        $cacheData = unserialize(base64_decode(file_get_contents($filePath)));
        if (isset($cacheData)) unset($cacheData[$keyName]);
        file_put_contents($filePath, base64_encode(serialize($cacheData)));
        return true;
    }

    public static function expires(string $key)
    {
        $filePath = self::getCacheFilePath($key, $keyName);
        $cacheData = unserialize(base64_decode(file_get_contents($filePath)));
        if ($cacheData === false) {
            return time() - 1;
        }
        return $cacheData[$keyName]['expires'];
    }


    private static function getCacheFilePath($key, &$keyname = '')
    {
        $keyMd5 = md5($key);
        list($dirName, $fileName, $keyname, $ds) = array(substr($keyMd5, 0, 2), substr($keyMd5, 2, 2), substr($keyMd5, 4, 28), DIRECTORY_SEPARATOR);
        $filePath = $_SERVER['DOCUMENT_ROOT'] . 'runtime' . $ds . 'cache' . $ds . $dirName;
        if (!is_dir($filePath)) {
            mkdir(iconv("UTF-8", "GBK", $filePath), 0777, true);
        }
        if (!is_file($filePath . $ds . $fileName)) {
            file_put_contents($filePath . $ds . $fileName, '');
        }
        return $filePath . $ds . $fileName;
    }
}

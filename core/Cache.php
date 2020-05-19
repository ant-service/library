<?php

namespace AntService;

use AntService\Src\Common\Config;

class Cache
{
    private static $returnObject = null;

    public static function get(string $key)
    {
        self::initType();
        return self::$returnObject::get($key);
    }

    public static function set(string $key, $value, int $expires = 7200)
    {
        self::initType();
        return self::$returnObject::set($key, $value, $expires);
    }

    private static function initType(): void
    {
        $className = 'AntService\Src\Cache\\' . ucfirst(Config::readEnv('CACHE_MODE'));
        self::$returnObject = new $className();
    }
}

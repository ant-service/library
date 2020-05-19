<?php

namespace AntService\Src\Interfaces;

interface Cache
{

    public static function get(string $key);

    public static function set(string $key,$value, int $expires = 7200);

}

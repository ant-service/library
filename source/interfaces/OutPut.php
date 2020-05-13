<?php

namespace AntService\Src\Interfaces;

interface OutPut
{

    public static function success($data): string;


    public static function error($code, string $message = ''): string;

    
    public static function initResponse(): void;
}

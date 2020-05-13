<?php

namespace AntService\Src\Output;

use AntService\Src\Common\DataType;
use AntService\Src\Interfaces\OutPut;

class Json implements OutPut
{
    public static function success($data): string
    {
        return DataType::convertJson($data, true);
    }

    public static function error($code, string $message = ''): string
    {
        return DataType::convertJson([
            'code' => $code,
            'msg' => $message,
        ]);
    }

    public static function initResponse(): void
    {
        header("Content-type: application/json; charset=utf-8");
    }
}

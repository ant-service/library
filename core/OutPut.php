<?php

namespace AntService;

use AntService\Src\Common\Config;

class OutPut
{

    private static $returnObject = null;

    /**
     * 成功返回
     * @param any $data 传入数据,支持任意类型
     * @author mahaibo <mahaibo@hongbang.js.cn>
     * @return array
     */
    public static function success($data): void
    {
        self::initResponse(200);
        exit(self::$returnObject::success($data));
    }

    /**
     * 失败返回
     * @param int|string $code 错误码
     * @param string $message 错误内容
     * @param integer $status http状态码
     * @author mahaibo <mahaibo@hongbang.js.cn>
     * @return array
     */
    public static function error($code, string $message = '', $status = 500): void
    {
        self::initResponse($status);
        exit(self::$returnObject::error($code, $message));
    }

    /**
     * 初始化相应结果
     * @author mahaibo <mahaibo@hongbang.js.cn>
     * @return void
     */
    private static function initResponse(int $httpStatus)
    {
        self::initType();
        header("HTTP/1.1 {$httpStatus}");
        self::$returnObject::initResponse();
    }

    private static function initType(): void
    {
        $className = 'AntService\Src\Output\\' . Config::readEnv('RETURN_DATE_FORMAT');
        self::$returnObject = new $className();
    }
}

<?php

namespace AntService;

use AntService\Src\Common\Config;
use AntService\Src\Common\DataType;

class Module
{
    private static $arguments = array();

    public static function use(string $moduleName, array $arguments = array())
    {
        $userDir = self::getUserDir();
        $moduleName = strtoupper($moduleName);
        self::setArguments($moduleName, $arguments);

        $moduleFile = $userDir . 'module/' . $moduleName;

        if (is_file($moduleFile)) {
            $isDownload = false;
        } else {
            $isDownload = true;
            self::download($moduleName, $moduleFile);
        }

        require_once $moduleFile;

        if (!class_exists($moduleName)) {
            OutPut::error('VERIFY_CLASS_FAIL', '验证模块类失败,该模块[' . $moduleName . ']未实现同名类');
        }
        $isDownload and self::firstLoad($moduleName);
        return new $moduleName();
    }

    private static function setArguments($moduleName, $arguments)
    {
        self::$arguments[$moduleName] = $arguments;
    }

    public static function getArguments(string $key = ''): array
    {
        if ($key == '') {
            return self::$arguments;
        }
        return self::$arguments[$key] ?? array();
    }

    /**
     * 模块首次载入/下载加载模块
     * @author mahaibo <mahaibo@hongbang.js.cn>
     * @param string $moduleName 模块名称
     * @return void
     */
    private static function firstLoad(string $moduleName): void
    {
        //载入数据库依赖信息
        $dbDepend = $moduleName::$dbDepend ?? array();
        $dataBaseModule = Config::readEnv('DATABASE_MODULE');
        $dataBaseModule !== $moduleName && $dbDepend != null
            and self::use($dataBaseModule)->syncDataBase($dbDepend) or OutPut::error('SYNC_DATABASE_FAIL', '同步数据库结构失败');

        //模块初始化操作
        is_callable([$moduleName, 'init']) and self::use($moduleName)::init();
    }

    /**
     * 下载模块
     * @author mahaibo <mahaibo@hongbang.js.cn>
     * @param string $moduleName 下载模块名称
     * @param string $moduleFile 模块存储位置
     * @return void
     */
    private static function download(string $moduleName, string $moduleFile): void
    {
        $result = NetworkRequest::postRequest(Config::readEnv('SERVICE_URL') . Config::readEnv('DOWNLOAD_MODULE'), ['token' => Config::read('userinfo.token'), 'moduleName' => $moduleName]);

        if ($result == false) OutPut::error('SEND_REQUEST_FAIL', '请求失败');

        $content = DataType::convertArray($result['content']);

        if ($result['status'] == 500) OutPut::error($content['code'], $content['msg']);

        if ($result['status'] == 200) if (!file_put_contents($moduleFile, base64_decode($content['content']))) OutPut::error('WRITE_MODULE_FAIL', '写入模块失败,请检查是否拥有写入权限[' . $moduleFile . ']');

        if ($result['status'] != 200 && $result['status'] != 500) OutPut::error('CAPTURE UNKNOWN ERROR', '出现位置错误');
    }

    private static function getUserDir()
    {
        if (function_exists('getUserCustomDir')) {
            return getUserCustomDir();
        }
        return getUserDir();
    }
}

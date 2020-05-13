<?php

namespace AntService;
use AntService\Src\Common\Config;

class Module
{
    private static $arguments = array();

    public static function use(string $moduleName, string $version, array $arguments = array())
    {
        $userDir = self::getUserDir();
        var_dump($userDir);exit();
        self::setArguments($moduleName,$arguments);
        
        $moduleFile = UNAS . AntRequest::getHost() . '/module/' . $moduleName;

        if(is_file($moduleFile)){
            $isDownload = false;
        }else{
            $isDownload = true;
            self::download($moduleName, $moduleFile);
        }
        
        require_once $moduleFile;

        if (!class_exists($moduleName)) {
            Output::errorOutput('VERIFY_CLASS_FAIL', '验证模块类失败,该模块[' . $moduleName . ']未实现同名类');
        }
        $isDownload and self::firstLoad($moduleName);
        return new $moduleName();
    }

    private static function setArguments($moduleName,$arguments)
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
            and self::use($dataBaseModule)->syncDataBase($dbDepend) or Output::errorOutput('SYNC_DATABASE_FAIL', '同步数据库结构失败');

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
        $downloadUrl = 'compress.zlib://' . Config::readEnv('SERVICE_URL') . '/module/' . $moduleName . '.msphp';
        $result = file_get_contents($downloadUrl);
        if (!$result) {
            Output::errorOutput('DOWNLOAD_MODULE_FAIL', '官方模块库中不存在此模块');
        }
        if (!file_put_contents($moduleFile, $result)) {
            Output::errorOutput('WRITE_MODULE_FAIL', '写入模块失败,请检查是否拥有写入权限[' . $moduleFile . ']');
        }
    }

    private static function getUserDir(){
        if(function_exists('getUserCustomDir')){
            return getUserCustomDir();
        }
        return getUserDir();

    }
}

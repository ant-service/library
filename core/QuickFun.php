<?php

use AntService\Cache;
use AntService\Module;
use AntService\OutPut;
use AntService\Src\Common\Config;
use AntService\Src\Common\DataType;
use AntService\Src\DataBase\Depend;
use AntService\Src\DataBase\PdoConn;

/** 获取用户目录地址 */
function getUserDir()
{
    return $_SERVER['DOCUMENT_ROOT'];
}

function useDB()
{
    if ($GLOBALS['pdo_conn']) return $GLOBALS['pdo_conn'];
    $GLOBALS['pdo_conn'] = new PdoConn();
    return useDB();
}

/**
 * 同步数据表
 * @param array $dbDepend 依赖规则 例：['user' => 'id,nickname,age', 'user_account' => 'id,uid,username,password']
 * @return boolean
 * @author mahaibo <mahaibo@hongbang.js.cn>
 */
function syncDataBase($dbDepend)
{
    return Depend::syncDataBase($dbDepend);
}

function convertArray($variate, string $delimiter = ',')
{
    return DataType::convertArray($variate, $delimiter);
}

/**
 * 成功输出
 * @param array|object|string|bool $result 输出数据,会自动转为Output模组指定类型
 * @return void
 */
function successOutput($result = array())
{
    OutPut::success($result);
}

/**
 * 错误输出
 * @param string|int $errorCode 错误码,数字错误码需转换String
 * @param string $errorMsg 错误内容，错误提示
 * @param integer $httpStatus http状态码
 * @return void
 */
function errorOutput($errorCode, string $errorMsg, int $httpStatus = 400)
{
    Output::error($errorCode, $errorMsg, $httpStatus);
}

function useModule(string $moduleName, array $payload = array())
{
    return Module::use($moduleName, $payload);
}

function getArguments(string $key = '')
{
    return Module::getArguments($key);
}

/**
 * 读取配置文件
 * @param string $configName 配置文件名称 
 * 例 Framework.OutputModule 可直接 Framework配置文件中的OutputModule值
 * 如值为数组则支持多级取值,以'.'连接
 * @return void
 */
function readConfig(string $configName): string
{
    return Config::read($configName);
}

function writeConfig(string $configName, $content)
{
    return Config::write($configName, $content);
}

function readEnv(string $configName)
{
    return Config::readEnv($configName);
}

function setCache($key, $value, $expires = 7200)
{
    return Cache::set($key, $value, $expires);
}

function getCache($key)
{
    return Cache::get($key);
}

function removeCache($key)
{
    return Cache::remove($key);
}

function setNotify($moduleName, $funcName)
{
    $uuid = getUUID();
}

function getUUID()
{
    $charid = strtoupper(md5(uniqid(rand(), true)));
    return substr($charid, 0, 8) . '-' . substr($charid, 8, 4) . '-' . substr($charid, 12, 4) . '-' . substr($charid, 16, 4) . '-' . substr($charid, 20, 12);
}

/**
 * 注册循环任务,同一模块下一个任务只能注册一次
 * @param string $moduleName 执行的模块名
 * @param string $funcName 执行的函数名
 * @param integer $interval 执行间隔,单位秒
 * @param integer $maxCount 最大执行次数,默认0为不限执行次数
 * @return void
 * @author mahaibo <mahaibo@hongbang.js.cn>
 */
function registerCyclicTask(string $moduleName, string $funcName, int $interval, int $maxCount = 0): bool
{
    $taskItems = (array) getCache('_system_tasks');
    $taskItems[$moduleName . '_' . $funcName] = array(
        'start_time' => date('Y-m-d H:i:s'),
        'last_execute_time' => 0,
        'execute_interval' => $interval,
        'execute_count' => 0,
        'max_execute_count' => $maxCount,
        'run_time' => 0,
        'module_name' => $moduleName,
        'func_name' => $funcName
    );
    setCache('_system_tasks', $taskItems);
    if (getCache('_system_tasks.' . $moduleName . '_' . $funcName)) return true;
    return false;
}

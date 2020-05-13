<?php

use AntService\OutPut;
use AntService\Src\Common\Config;
use AntService\Src\Common\DataType;

/** 获取用户目录地址 */
function getUserDir()
{
    return $_SERVER['DOCUMENT_ROOT'];
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

function getPayload(string $key = '')
{
    return Module::getPayload($key);
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
    return useModule(readEnv('CACHE_MODULE'))::set($key, $value, $expires);
}

function getCache($key)
{
    return useModule(readEnv('CACHE_MODULE'))::get($key);
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

/**
 * Undocumented function
 * @param string $taskName
 * @param string $moduleName 
 * @param string $funcName 函数名
 * @param integer $msec 多少秒后执行
 * @return void
 * @author mahaibo <mahaibo@hongbang.js.cn>
 */
function registerAfterTask(string $taskName, string $moduleName, string $funcName, int $msec): bool
{
    return true;
}

function unregisterTask(string $taskName, string $moduleName): bool
{
    return true;
}

function getTaskInfo(string $taskName, string $moduleName): array
{
    return array();
}

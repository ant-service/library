<?php

namespace AntService\Src\DataBase;

use AntService\OutPut;
use AntService\Src\Common\Config;
use Exception;
use PDO;
use PDOException;

class PdoConn
{
    private static $conn = null;

    /**
     * 获取PDO连接
     * @return PDO
     */
    public static function getConnect(): PDO
    {
        $databaseType = Config::readEnv('PDO_DATABASE');
        if (self::$conn != null) {
            return self::$conn;
        }
        if (!class_exists('pdo')) {
            OutPut::error('VERIFY_PDO_FAIL', '验证PDO失败,请开启PDO拓展');
        }
        $database = Config::read('DataBase');
        $host = $database['HostAddress'] ?? '127.0.0.1';
        $port = $database['HostPort'] ?? 3306;
        $dbName = $database['DbName'] ?? '';
        try {
            return self::$conn = new PDO(
                "{$databaseType}:host={$host}:{$port};dbname={$dbName}",
                $database['UserName'],
                $database['Password'],
                array(PDO::ATTR_PERSISTENT => true)
            );
        } catch (Exception $e) {
            errorOutput('CONNECT_SQL_ERROR', iconv('gbk', 'utf-8', $e->getMessage()));
        }
    }
    /**
     * 开启事务
     * @return void
     */
    public static function startTransaction(): void
    {
        self::getConnect()->beginTransaction();
    }

    /**
     * 事务回滚
     * @return void
     */
    public static function rollback(): void
    {
        self::getConnect()->rollBack();
    }

    /**
     * 事务提交
     * @return void
     */
    public static function commit(): void
    {
        self::getConnect()->commit();
    }

    /**
     * 执行Sql语句
     * @param string $sqlStr 待执行的Sql语句
     * @return integer 受影响行数
     */
    public static function execute(string $sqlStr): int
    {
        try {
            $result = self::getConnect()->exec($sqlStr);
            if ($result === false) OutPut::error('EXECUTE_MYSQL_ERROR', '' . self::getConnect()->errorInfo()[2] ?? '');
            return $result;
        } catch (PDOException $e) {
            OutPut::error('EXECUTE_MYSQL_ERROR', '执行Mysql错误,错误结果:' . $e->getMessage());
        }
    }

    /**
     * 查询Sql语句
     * @param string $sqlStr 待查询的Sql语句
     * @return array 查询的结果
     */
    public static function query(string $sqlStr, $ignoreError = false): array
    {
        try {
            $result = self::getConnect()->query($sqlStr);
            if ($ignoreError && $result === false) return array();
            if ($result === false) OutPut::error('QUERY_MYSQL_ERROR', '' . self::getConnect()->errorInfo()[2] ?? '');
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            OutPut::error('QUERY_MYSQL_ERROR', '查询Mysql错误,错误结果:' . iconv('gbk', 'utf-8', $e->getMessage()));
        }
    }
}

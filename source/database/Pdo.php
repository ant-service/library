<?php

class MYSQL_CORE_OTS
{
    private static $conn = null;

    /**
     * 获取PDO连接
     * @return PDO
     */
    public static function getConnect(): PDO
    {
        if (self::$conn != null) {
            return self::$conn;
        }
        if (!class_exists('pdo')) {
            Output::errorOutput('VERIFY_PDO_FAIL', '验证PDO失败,请开启PDO拓展');
        }
        $database = Config::read('DataBase');
        $host = $database['HostAddress'] ?? '127.0.0.1';
        $port = $database['HostPort'] ?? 3306;
        $dbName = $database['DbName'] ?? '';
        return self::$conn = new PDO(
            "mysql:host={$host}:{$port};dbname={$dbName}",
            $database['UserName'],
            $database['Password'],
            array(PDO::ATTR_PERSISTENT => true)
        );
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
            return self::getConnect()->exec($sqlStr);
        } catch (PDOException $e) {
            errorOutput('EXECUTE_MYSQL_ERROR', '执行Mysql错误,错误结果:' . $e->getMessage());
        }
    }

    /**
     * 查询Sql语句
     * @param string $sqlStr 待查询的Sql语句
     * @return array 查询的结果
     */
    public static function query(string $sqlStr): array
    {
        try {
            $result = self::getConnect()->query($sqlStr);
            if (!$result) {
                return array();
            }
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            errorOutput('QUERY_MYSQL_ERROR', '查询Mysql错误,错误结果:' . $e->getMessage());
        }
    }
}

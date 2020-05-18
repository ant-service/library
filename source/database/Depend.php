<?php

namespace AntService\Src\DataBase;

use AntService\Src\Common\Config;

class Depend
{
    /**
     * 同步数据表
     * @param array $dbDepend 依赖规则 例：['user' => 'id,nickname,age', 'user_account' => 'id,uid,username,password']
     * @return boolean
     * @author mahaibo <mahaibo@hongbang.js.cn>
     */
    public static function syncDataBase($dbDepend): bool
    {
        $serviceUrl = Config::readEnv('SERVICE_URL') . '/dbstruct/';
        $dataBaseConfig = Config::read('database');
        foreach ($dbDepend as $table => $fields) {
            $tableInfo = PdoConn::query("show columns from {$table}", true);
            $tableFields = array();
            foreach ($tableInfo as $fieldInfo) {
                $tableFields[] = $fieldInfo['Field'];
            }
            $missField = array();
            foreach (convertArray($fields) as $field) {
                in_array($field, $tableFields) or $missField[] = $field;
            }
            $allowFields = file_get_contents($serviceUrl . $table . '.json');
            if ($allowFields === false) {
                errorOutput('GET_RESOURCE_FAIL', '官方数据表库不存在此数据表');
            }
            $allowFields = convertArray($allowFields);
            $createSql = '';
            $createArray = array();
            if ($tableInfo == null) {
                foreach ($missField as $field) {
                    $fieldInfo = $allowFields[$field] ?? errorOutput('SET_FIELD_FAIL', '设置字段失败,官方数据库表[' . $table . ']目前不支持字段[' . $field . ']');
                    $sonArray = array(
                        $field,
                        $fieldInfo['type'] . '(' . $fieldInfo['length'] . ')',
                        $fieldInfo['is_pk'] ? 'primary key' : '',
                        'default',
                        is_string($fieldInfo['default']) ?  "'" . $fieldInfo['default'] . "'" : $fieldInfo['default'],
                        'comment',
                        "'" . $fieldInfo['comment'] . "'"
                    );
                    $createArray[] = implode(' ', $sonArray);
                }

                $createSql = 'create table if not exists ' . $table . '('
                    . implode(',', $createArray)
                    . ')engine=' . $dataBaseConfig['Engine'] . ' default charset=' . $dataBaseConfig['Charset'];
            } else {
                foreach ($missField as $field) {
                    $fieldInfo = $allowFields[$field] ?? errorOutput('SET_FIELD_FAIL', '设置字段失败,官方数据库表[' . $table . ']目前不支持字段[' . $field . ']');
                    $sonArray = array(
                        'add',
                        $field,
                        $fieldInfo['type'] . '(' . $fieldInfo['length'] . ')',
                        $fieldInfo['is_pk'] ? 'primary key' : '',
                        'default',
                        is_string($fieldInfo['default']) ?  "'" . $fieldInfo['default'] . "'" : $fieldInfo['default'],
                        'comment',
                        "'" . $fieldInfo['comment'] . "'"
                    );
                    $createArray[] = implode(' ', $sonArray);
                }
                $createSql = 'ALTER TABLE ' . $table . ' '
                    . implode(',', $createArray);
            }
            PdoConn::execute($createSql);
        }
        return true;
    }
}

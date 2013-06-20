<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Database;
use CPath\Interfaces\IDatabase;
use \PDO;
abstract class PDOTable {
    const TableName = NULL;
    static function getSQLInsertInto($_args) {
        return "INSERT INTO ".static::TableName." (".implode(', ', func_get_args()).") VALUES ";
    }
    static function getSQLInsertValues($_args) {
        return "\n(".implode(', ', func_get_args()).")";
    }

    static function select(\PDO $DB, $select, $where, $limit='1') {
        return $DB->query("SELECT ".self::parseSelect($select)
            ."\nFROM ".static::TableName
            ."\nWHERE ".self::parseWhere($DB, $where)
            .($limit ? "\nLIMIT ".$limit : ''));
    }

    private static function parseSelect($select) {
        if(!is_array($select))
            return $select;
        $sql = '';
        foreach($select as $name=>$param) {
            $sql .= ($sql ? ', ' : '') . $param . (is_int($name) ? '' : $name);
        }
        return $sql;
    }

    private static function parseWhere(\PDO $DB, $where) {
        if(!is_array($where))
            return $where;
        $sql = '';
        foreach($where as $name=>$param) {
            $sql .= ($sql ? "\n\tAND " : '') . (is_int($name) ? $param : $name."='".$DB->quote($param)."'");
        }
        return $sql;
    }
}
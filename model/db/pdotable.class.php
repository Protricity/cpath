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

    static function select(\PDO $DB, $_selectArgs) {
        $args = func_get_args();
        return new PDOSelect(static::TableName, array_shift($args), $args);
    }

    static function update(\PDO $DB, $_fieldArgs) {
        $args = func_get_args();
        return new PDOUpdate(static::TableName, array_shift($args), $args);
    }

    static function insert(\PDO $DB, $_fieldArgs) {
        $args = func_get_args();
        return new PDOInsert(static::TableName, array_shift($args), $args);
    }

    static function from($alias) {
        return "\nFROM ".static::TableName." {$alias}";
    }

    static function join($alias, $on='') {
        return "\nJOIN ".static::TableName." {$alias} ON {$on}";
    }
//
//    static function select(\PDO $DB, $select, $where, $limit='1') {
//        return $DB->query("SELECT ".self::parseList($select)
//            ."\nFROM ".static::TableName
//            ."\nWHERE ".self::parseSet($DB, $where)
//            .($limit ? "\nLIMIT ".$limit : ''));
//    }

}
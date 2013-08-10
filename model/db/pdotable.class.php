<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;
use CPath\Interfaces\IDatabase;
use \PDO;
abstract class PDOTable {
    const TableName = NULL;

    static function select(\PDO $DB, $_selectArgs) {
        $args = func_get_args();
        return new PDOSelect(static::TABLE, array_shift($args), $args);
    }

    static function update(\PDO $DB, $_fieldArgs) {
        $args = func_get_args();
        return new PDOUpdate(static::TABLE, array_shift($args), $args);
    }

//
//    static function select(\PDO $DB, $select, $where, $limit='1') {
//        return $DB->query("SELECT ".self::parseList($select)
//            ."\nFROM ".static::TABLE
//            ."\nWHERE ".self::parseSet($DB, $where)
//            .($limit ? "\nLIMIT ".$limit : ''));
//    }

}
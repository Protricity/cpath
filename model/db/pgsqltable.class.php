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
abstract class PGSQLTable extends PDOTable {

    static function insert(\PDO $DB, $_fieldArgs) {
        $args = func_get_args();
        return new PGSQLInsert(static::TableName, array_shift($args), $args);
    }

}
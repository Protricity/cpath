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
    const Name = NULL;
    static function getSQLInsertInto($_args) {
        return "INSERT INTO ".static::Name." (".implode(', ', func_get_args()).") VALUES ";
    }
    static function getSQLInsertValues($_args) {
        return "\n(".implode(', ', func_get_args()).")";
    }
}
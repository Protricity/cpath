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
class MySQLInsert extends PDOInsert {


    protected function updateSQL(&$SQL) {}

    public function requestInsertID($field) {
        return $this;
    }

    public function getInsertID() {
        return $this->DB->lastInsertId();
    }
}
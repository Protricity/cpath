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
class PGSQLInsert extends PDOInsert {
    private $returning=NULL;

    public function returning($field) {
        $this->returning = $field;
        return $this;
    }

    protected function updateSQL(&$SQL) {
        if($this->returning)
            $SQL .= "\nRETURNING ".$this->returning;
    }

    public function requestInsertID($field) {
        return $this->returning($field);
    }

    public function getInsertID() {
        return $this->stmt->fetchColumn(0);
    }

}
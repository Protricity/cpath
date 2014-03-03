<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Query;

class PGSQLInsert extends PDOInsert {
    private $returning=NULL;

    protected function updateSQL(&$SQL) {
        if($this->returning)
            $SQL .= "\nRETURNING ".$this->returning;
    }

    public function requestInsertID($field) {
        $this->returning = $field;
        return $this;
    }

    public function getInsertID() {
        if(!$this->returning)
            throw new \Exception("Insert ID was not previously requested");
        return $this->stmt->fetchColumn(0);
    }

}
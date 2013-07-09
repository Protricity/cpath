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
class PDOInsert {
    private $DB;
    /** @var \PDOStatement */
    private $stmt=NULL;
    private $table;
    private $fields=array();
    private $returning=NULL;
    private $batch=NULL;

    public function __construct($table, \PDO $DB, Array $fields) {
        $this->DB = $DB;
        $this->table = $table;
        $this->fields = $fields;
    }

    public function addField($field) {
        $this->fields[] = $field;
        $this->stmt = NULL;
        return $this;
    }

    public function returning($field) {
        $this->returning = $field;
        return $this;
    }

    public function batch() {
        $this->batch = array();
        return $this;
    }

    public function values($_values) {
        if(!is_array($_values)) $_values = func_get_args();
        if(!$this->stmt) $this->stmt = $this->DB->prepare($this->getSQL());
        if($this->batch !== NULL) {
            $this->batch[] = $_values;
        } else {
            $this->stmt->execute($_values);
        }
        return $this;
    }

    public function commit() {
        if(!$this->batch)
            throw new \Exception("No Batch Available");
        $SQL = "INSERT INTO ".$this->table
            ."\n (".implode(', ',$this->fields).')'
            ."\n VALUES ";
        $values = array();
        foreach($this->batch as $i => $batch) {
            $SQL .= ($i ? ',' : '')."\n\t(?".str_repeat(', ?', sizeof($batch)-1).')';
            $values = array_merge($values, $batch);
        }
        if($this->returning)
            $SQL .= "\nRETURNING ".$this->returning;
        $this->stmt = $this->DB->prepare($SQL);
        $this->stmt->execute($values);
        return $this;
    }

    public function getInsertID() {
        return $this->stmt->fetchColumn(0);
    }

    public function getSQL($token='?') {
        return "INSERT INTO ".$this->table
            ."\n (".implode(', ',$this->fields).')'
            ."\nVALUES (".$token.str_repeat(', '.$token, sizeof($this->fields)-1).')'
            .($this->returning ? "\nRETURNING ".$this->returning : '');
    }
}
<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;
use CPath\Base;
use CPath\Interfaces\IDatabase;
use CPath\Log;
use \PDO;
abstract class PDOInsert {
    protected $DB;
    /** @var \PDOStatement */
    protected $stmt=NULL;
    private $table;
    private $fields=array();
    private $batch=NULL;

    abstract protected function updateSQL(&$SQL);

    /**
     * @param $field String name of insert ID Field
     * @return PDOInsert
     */
    abstract public function requestInsertID($field);
    abstract public function getInsertID();

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

    public function batch() {
        $this->batch = array();
        return $this;
    }

    /**
     * Insert or batch a row of values
     * @param $_values Array an indexed array of values to insert
     * @return PDOInsert $this
     */
    public function values($_values) {
        if(!is_array($_values)) $_values = func_get_args();
        if($this->batch !== NULL) {
            $this->batch[] = $_values;
        } else {
            $sql = $this->getSQL();
            if(!$this->stmt) $this->stmt = $this->DB->prepare($sql);
            if(Base::isDebug())
                Log::v2(__CLASS__, $sql);
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
        $this->updateSQL($SQL);
        $this->stmt = $this->DB->prepare($SQL);
        $this->stmt->execute($values);
        if(Base::isDebug())
            Log::v2(__CLASS__, $SQL);
        return $this;
    }

    public function getSQL($token='?') {
        if(!$this->fields)
            throw new \Exception("No field provided for insert");
        $SQL = "INSERT INTO ".$this->table
            ."\n (".implode(', ',$this->fields).')'
            ."\nVALUES (".$token.str_repeat(', '.$token, sizeof($this->fields)-1).')';
        $this->updateSQL($SQL);
        if(Base::isDebug())
            Log::v2(__CLASS__, $SQL);
        return $SQL;
    }
}
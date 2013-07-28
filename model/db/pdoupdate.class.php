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
class PDOUpdate extends PDOWhere {
    /** @var \PDO */
    private $DB;
    /** @var \PDOStatement */
    private $stmt=NULL;
    private $fields=array(), $limit;
    public function __construct($table, \PDO $DB, Array $fields, $limit=NULL) {
        parent::__construct($table);
        $this->DB = $DB;
        $this->fields = $fields;
        $this->limit = $limit;
    }

    public function addField($field) {
        $this->fields[] = $field;
        $this->stmt = NULL;
        return $this;
    }

    public function addWhere($field) {
        if(strpos($field, '?') === false)
            $field .= '=?';
        $this->where[] = $field;
        $this->stmt = NULL;
        return $this;
    }

    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function values($_values) {
        if(!is_array($_values)) $_values = func_get_args();
        if(!$this->stmt) $this->stmt = $this->DB->prepare($this->getSQL());
        if($this->values) $_values = array_merge($_values, $this->values);
        $this->stmt->execute($_values);
        return $this;
    }

    public function getLastAffectedRows() { return $this->stmt->rowCount(); }

    public function getSQL() {
        if(!$this->where)
            throw new \Exception("method addWhere() was not called");
        $SQL = "UPDATE ".$this->table
            ."\nSET ".implode('=?, ',$this->fields).'=?'
            .parent::getSQL()
            .($this->limit ? "\nLIMIT ".$this->limit : "");

        if(Base::isDebug())
            Log::v(__CLASS__, $SQL);
        return $SQL;
    }
}
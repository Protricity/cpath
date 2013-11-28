<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;
use CPath\Config;
use CPath\Log;
use PDO;

class PDOUpdate extends PDOWhere {
    /** @var \PDO */
    private $DB;
    /** @var \PDOStatement */
    private $mStmt=NULL;
    private $fields=array(), $mLimit=NULL;
    public function __construct($table, \PDO $DB, Array $fields, $limit=NULL) {
        parent::__construct($table);
        $this->DB = $DB;
        $this->fields = $fields;
        $this->limit = $limit;
    }

    public function addField($field) {
        $this->fields[] = $field;
        $this->mStmt = NULL;
        return $this;
    }

    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function values($_values) {
        if(!is_array($_values)) $_values = func_get_args();
        if(!$this->mStmt) {
            $sql = $this->getSQL();
            $this->mStmt = $this->DB->prepare($sql);
            if(Config::$Debug)
                Log::v2(__CLASS__, $sql);
        }
        if($this->mValues) $_values = array_merge($_values, $this->mValues);
        $this->mStmt->execute($_values);
        return $this;
    }

    public function getLastAffectedRows() { return $this->mStmt->rowCount(); }

    public function getSQL() {
        if(!$this->mWhere)
            throw new \Exception("method where() was not called");
        $SQL = "UPDATE ".$this->mTable
            ."\nSET ".implode('=?, ',$this->fields).'=?'
            .parent::getSQL()
            .($this->mLimit ? "\nLIMIT ".$this->mLimit : "");

//        if(Config::$Debug)
//            Log::v2(__CLASS__, $SQL);
        return $SQL;
    }
}
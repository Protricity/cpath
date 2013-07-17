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
class PDODelete {
    /** @var \PDO */
    private $DB;
    /** @var \PDOStatement */
    private $stmt=NULL;
    private $table, $where=array(), $limit='1';
    public function __construct($table, \PDO $DB) {
        $this->DB = $DB;
        $this->table = $table;
    }

    public function where($field, $value=NULL) {
        if(!is_int($value))
            $value = $this->DB->quote($value);
        if(strpos($field, '?') === false)
            $field .= '=' . $value;
        else
            $field = str_replace('?', $value, $field);
        $this->where[] = $field;
        $this->stmt = NULL;
        return $this;
    }


    public function execute() {
        if(!$this->stmt) $this->stmt = $this->DB->prepare($this->getSQL());
        $this->stmt->execute();
        return $this;
    }

    public function getDeletedRows() { return $this->stmt->rowCount(); }

    public function getSQL() {
        if(!$this->where)
            throw new \Exception("method where() was not called");
        return "DELETE FROM ".$this->table
            ."\nWHERE ".($this->where ? implode(' AND ', $this->where) : '1');
    }
}
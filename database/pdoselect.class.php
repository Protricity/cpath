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
class PDOSelect implements \IteratorAggregate {
    private $DB, $table, $select=array(), $where=array(), $values=array(), $limit='1';
    public function __construct($table, \PDO $DB, Array $select=array()) {
        $this->DB = $DB;
        $this->table = $table;
        foreach($select as $name=>$field)
            $this->select($field, is_int($name) ? NULL : $name);
    }

    public function select($field, $name=NULL) {
        $this->select[$name ?: $field] = $field;
        return $this;
    }

    public function where($field, $value=NULL) {
        if($value !== NULL) {
            $this->values[] = $value;
            if(strpos($field, '?') === false)
                $field .= '=?';
        }
        $this->where[] = $field;
        return $this;
    }

    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function exec() {
        $stmt = $this->DB
            ->prepare($this->getSQL());
        $stmt->execute($this->values);
        return $stmt;
    }

    public function fetchColumn($i=0) {
        return $this->exec()
            ->fetchColumn($i);
    }

    public function fetch() {
        return $this->exec()
            ->fetch();
    }

    public function getSQL() {
        return "SELECT ".implode(', ', $this->select)
            ."\nFROM ".$this->table
            ."\nWHERE ".($this->where ? implode(' AND ', $this->where) : '1')
            ."\nLIMIT ".$this->limit;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return $this->exec();
    }
}
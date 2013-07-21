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
class PDOSelect implements \Iterator {
    private $DB, $table, $select=array(), $where=array(), $values=array(), $limit='1';
    /** @var \PDOStatement */
    private $stmt = null;
    private $row = null;
    private $count = 0;
    private $customMethod = null;
    private $lastCond = true;
    private $alias = NULL;
    private $joins = array();

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

    /**
     * @param String $table the table to join
     * @param String $sourceField The source field to join on. If $destField is omited, $sourceField represents the entire " ON ..." segment of the join.
     * @param String|null $destField The destination field to join on.
     * @param String|null $alias The alias for the table
     * @return $this
     */
    public function leftJoin($table, $sourceField, $destField=NULL, $alias=NULL) {
        if($destField != NULL) {
            if($alias) $table .= ' '.$alias;
            else $alias = $table;
            $sAlias = $this->alias ?: $this->table;
            $sourceField = "{$sAlias}.{$sourceField}";
            $destField = "{$alias}.{$destField}";
            $sourceField = "ON {$sourceField} = {$destField}";
        } else {
            if($alias) $table .= ' '.$alias;
        }

        $this->joins[] = "\nLEFT JOIN {$table} {$sourceField}";
        return $this;
    }

    public function where($field, $value=NULL, $alias=NULL) {
        if($value !== NULL) {
            if(!$alias) $alias = $this->table;
            $this->values[] = $value;
            if(strpos($field, '?') === false) {
                $e = '=';
                if(preg_match('/([=<>!]+|like)\s*$/i', $field))
                    $e = ' ';
                $field = $alias . '.' . $field . $e . '?';
            }
        }
        if(preg_match('/^AND|OR|\(|\)$/i', $field)) {
            if($field == '(' && !$this->lastCond)
                $this->where[] = 'AND';
            $this->lastCond = true;
            $this->where[] = $field;
            return $this;
        }
        if (!$this->lastCond) {
            $this->where[] = 'AND';
        }
        $this->where[] = $field;
        $this->lastCond = false;
        return $this;
    }


    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param $callable
     * @return $this
     */
    public function setCallback($callable) {
        $this->customMethod = $callable;
        return $this;
    }

    public function exec() {
        $sql = $this->getSQL();
        $this->stmt = $this->DB
            ->prepare($sql);
        $this->stmt->execute($this->values);
        $this->count=-1;
        return $this->stmt;
    }

    public function fetchColumn($i=0) {
        if(!$this->stmt) $this->exec();
        $this->count++;
        return $this->stmt->fetchColumn($i);
    }

    public function fetch() {
        if(!$this->stmt) $this->exec();
        $this->count++;
        $this->row = $this->stmt->fetch();
        if($this->row && $call = $this->customMethod)
            $this->row = $call instanceof \Closure ? $call($this->row) : call_user_func($call, $this->row);
        return $this->row;
    }

    public function fetchAll() {
        if(!$this->stmt) $this->exec();
        $fetch = $this->stmt->fetchAll();
        if($fetch && $call = $this->customMethod)
            foreach($fetch as &$row)
                $row = $call instanceof \Closure ? $call($row) : call_user_func($call, $row);
        $this->count = sizeof($fetch);
        return $fetch;
    }

    public function getSQL() {
        return "SELECT ".implode(', ', $this->select)
            ."\nFROM ".$this->table
            .implode('', $this->joins)
            ."\nWHERE ".($this->where ? implode(' ', $this->where) : '1')
            ."\nLIMIT ".$this->limit;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->row;
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->fetch();
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->count;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->row ? true : false;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->stmt = null;
        $this->exec();
    }
}
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
use CPath\Config;
use \PDO;
class PDOSelect extends PDOWhere implements \Iterator, \Countable {
    /** @var \PDOStatement */
    protected $mStmt=NULL;
    private $mDB, $mSelect=array(), $mLimit='1', $mOffset=NULL;
    private $mRow = null;
    private $mCount = 0;
    private $mCustomMethod = null;

    public function __construct($table, \PDO $DB, Array $select=array()) {
        parent::__construct($table);
        $this->mDB = $DB;
        foreach($select as $field)
            $this->select($field);
    }

    public function select($field, $alias=NULL) {
        if(!preg_match('/[.()]/', $field))
            $field = ($alias ?: $this->mAlias) . '.' . $field;
        $this->mSelect[] = $field;
        return $this;
    }

    public function limit($limit) {
        $this->mLimit = $limit;
        return $this;
    }

    public function offset($offset) {
        $this->mOffset = $offset;
        return $this;
    }

    public function page($page) {
        if(!$this->mLimit)
            throw new \Exception("For pagination, limit must be set first");
        $this->mOffset = $page > 1 ? ($page - 1) * $this->mLimit : 0;
        return $this;
    }

    /**
     * @param $callable
     * @return $this
     */
    public function setCallback($callable) {
        $this->mCustomMethod = $callable;
        return $this;
    }

    public function exec() {
        $sql = $this->getSQL();
        if(Config::$Debug)
            Log::v2(__CLASS__, $sql);
        $this->mStmt = $this->mDB
            ->prepare($sql);
        $this->mStmt->execute($this->mValues);
        $this->mCount=-1;
        return $this->mStmt;
    }

    public function fetchColumn($i=0) {
        if(!$this->mStmt) $this->exec();
        $this->mCount++;
        return $this->mStmt->fetchColumn($i);
    }

    public function fetch() {
        if(!$this->mStmt) $this->exec();
        $this->mCount++;
        $this->mRow = $this->mStmt->fetch();
        if($this->mRow && $call = $this->mCustomMethod)
            $this->mRow = $call instanceof \Closure ? $call($this->mRow) : call_user_func($call, $this->mRow);
        return $this->mRow;
    }

    public function fetchObject($Class) {
        if(!$this->mStmt) $this->exec();
        $this->mCount++;
        $this->mRow = $this->mStmt->fetchObject($Class);
        return $this->mRow;
    }

    public function fetchAll() {
        if(!$this->mStmt) $this->exec();
        $fetch = array();
        while($mRow = $this->fetch())
            $fetch[] = $mRow;

        $this->mCount = sizeof($fetch);
        return $fetch;
    }

    public function getSQL() {
        return "SELECT ".implode(', ', $this->mSelect)
            ."\nFROM ".$this->mTable
            .parent::getSQL()
            .($this->mLimit ? "\nLIMIT ".$this->mLimit : '')
            .($this->mOffset ? "\nOFFSET ".$this->mOffset : '');
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->mRow;
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
        return $this->mCount;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->mRow ? true : false;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->mStmt = null;
        $this->fetch();
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count() {
        if(!$this->mStmt) $this->exec();
        return $this->mStmt->rowCount();
    }
}
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
    private $mDistinct = false;
    private $mRow = null;
    private $mCount = 0;
    private $mCustomMethod = null;
    private $mParse = array();

    public function __construct($table, \PDO $DB, Array $select=array()) {
        parent::__construct($table);
        $this->mDB = $DB;
        foreach($select as $field)
            self::select($field);
    }

    /**
     * @param $field
     * @param String|null $alias The table alias to prepend to the $field. If $value is not set or $field contains
     * characters in '.()', then the alias will not be prepended to the field.
     * If the string '{}' appears, it will be replaced with the alias
     * @param null $name
     * @return $this
     */
    public function select($field, $alias=NULL, $name=NULL) {
        $field = $this->getAliasedField($field, $alias);
        if($name)
            $field .= ' "' . $name .'"';
        $this->mSelect[] = $field;
        return $this;
    }

    public function selectPrefixed($prefix, $field, $alias=NULL, $name=NULL) {
        if(!$name)
            $name = $field;
        $name = $prefix . '.' . $name;
        $this->mParse[] = $name;
        return self::select($field, $alias, $name);
    }

    public function distinct($on=true) {
        $this->mDistinct = $on ? true : false;
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
        if($this->mRow) {
            foreach($this->mParse as $name) {
                $path = explode('.', $name);
                $last = array_pop($path);
                $value = &$this->mRow[$name];
                unset($this->mRow[$name]);
                $target = &$this->mRow;
                foreach($path as $p) {
                    if(!isset($target[$p]))
                        $target[$p] = array();
                    $target = &$target[$p];
                }
                $target[$last] = $value;
            }
            if($call = $this->mCustomMethod)
                $this->mRow = $call instanceof \Closure ? $call($this->mRow) : call_user_func($call, $this->mRow);
        }
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
        $d = $this->mDistinct ? 'DISTINCT ' : '';
        return "SELECT " . $d . implode(', ', $this->mSelect)
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
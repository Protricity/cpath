<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/21/2014
 * Time: 7:33 PM
 */
namespace CPath\Data\Schema\PDO;

use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;

class PDOSelectBuilder extends PDOWhereBuilder implements ISequenceMap, \Iterator, \Countable
{
	private $mCurRow = null;
	private $mCurIndex = -1;
	private $mCount = null;
	private $mSelectSQL = null;

	public function select($column, $alias=null, $format=null) {
		if (is_array($column)) {
			foreach ($column as $k => $v)
				$this->select($v, is_int($k) ? null : $k, $format);
			return $this;
		}
		if($format)
			$column = sprintf($format, $column, $alias);
		else if($alias !== null)
			$column .= " as " . $alias;

		if ($this->mSelectSQL) {
			$this->mSelectSQL .= ', ' . $column;
		} else {
			$this->mSelectSQL = "SELECT " . $column;
		}

		return $this;
	}

	protected function getSQL() {
		if(!$this->mTableSQL)
			throw new \InvalidArgumentException("Table not set");
//		if(!$this->mSelectSQL)
//			throw new \InvalidArgumentException("Select not set");

		if($this->mFormat)
			return sprintf($this->mFormat, $this->mSelectSQL, $this->mTableSQL, $this->mWhereSQL, $this->mLimitSQL);

		return
			($this->mSelectSQL ?: "SELECT * ")
			. ("\n\tFROM " . $this->mTableSQL)
			. ($this->mWhereSQL)
			. ($this->mLimitSQL);
	}


	public function fetch($fetch_style = null, $cursor_orientation = \PDO::FETCH_ORI_NEXT, $cursor_offset = 0) {
		$this->execute();
		$stmt = $this->prepare();
		$this->mCurIndex++;
		return $stmt->fetch($fetch_style, $cursor_orientation, $cursor_offset);
	}

	public function fetchColumn($column_number = 0) {
		$this->execute();
		$stmt = $this->prepare();
		$this->mCurIndex++;
		return $stmt->fetchColumn($column_number);
	}

	public function fetchAll($fetch_style = null, $fetch_argument = null, Array $ctor_args = array()) {
		$this->execute();
		$stmt = $this->prepare();
		$this->mCurIndex++;
		return $stmt->fetchAll($fetch_style, $fetch_argument, $ctor_args);
	}

	function fetchOne($fetch_style = null) {
		$result = $this->fetchAll($fetch_style);
		if(!$result || sizeof($result) < 0)
			throw new \PDOException("Results not found");
		if(sizeof($result) > 1)
			throw new \PDOException("More than 1 Result found: ");
		return $result[0];
	}

	/**
	 * Map sequential data to the map
	 * @param ISequenceMapper $Map
	 * @param int $offset
	 * @param int $limit
	 */
	function mapSequence(ISequenceMapper $Map, $offset=0, $limit=100) {
		$limit = "LIMIT {$offset} {$limit}";
		$this->limit($limit, $offset);
		$stmt = $this->prepare();
		$i = $offset;
		while($Row = $stmt->fetch()) {
			$ret = $Map->mapNext($Row, $i++);
			if($ret === true)
				break;
		}
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current() {
		return $this->mCurRow ?: $this->mCurRow = $this->fetch();
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next() {
		$this->mCurRow = $this->fetch();
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key() {
		return $this->mCurIndex;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Checks if current position is valid
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid() {
		return $this->current() ? true : false;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind() {
		$this->mCurIndex = -1;
		$this->execute();
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
		return $this->mCount ?: $this->mCurIndex + 1;
	}
}


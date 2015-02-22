<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/19/2014
 * Time: 4:01 PM
 */
namespace CPath\Data\Schema\PDO;

use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Data\Schema\IReadableSchema;
use CPath\Data\Schema\IWritableSchema;
use CPath\Data\Schema\TableSchema;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;
use CPath\Request\Log\ILogListener;

define('AbstractPDOTable', __NAMESPACE__ . '\\AbstractPDOTable');
abstract class AbstractPDOTable implements ISequenceMap, IReadableSchema, ILogListener
{
	const className = AbstractPDOTable;

	const SELECT_COLUMNS = '*';
	const INSERT_COLUMNS = null;
	const UPDATE_COLUMNS = null;

	const TABLE_NAME = null;
	const FETCH_MODE = \PDO::FETCH_CLASS;
	const FETCH_CLASS = null;

	const DEFAULT_LIMIT = 25;

	/** @var ILogListener[] */
	protected $mLogListeners = array();

//	final public function __construct() {
//
//	}

	/**
	 * @return \PDO
	 */
	abstract function getDatabase();

	/**
	 * @return IReadableSchema
	 */
	function getSchema() { throw new \InvalidArgumentException("Not implemented"); }

	function getTableName() { return static::TABLE_NAME; }

	function getInsertStatement($insert, $format=null) {
		$DB = $this->getDatabase();
		$tableName = static::TABLE_NAME;
		$sql = "INSERT INTO {$tableName}";
		if(is_array($insert)) {
			foreach($insert as $k =>&$v)
				$v = $DB->quote($v);
			if(isset($insert[0])) {
				$sql .= ' VALUES (' . implode(', ', $insert) . ')';
			} else {
				$sql .= ' (\'' . implode("', '", array_keys($insert)) . '\') VALUES (' . implode(', ', $insert) . ')';
			}
		} else {
			$sql .= $insert;
		}
		if($format)
			$sql = sprintf($format, $sql);
		return $this->prepare($sql);
	}


	function getUpdateStatement($set, $where, $format=null) {
		$DB = $this->getDatabase();

		if(is_array($set)) {
			foreach($set as $k=>&$v)
				$v = "{$k} = " . $DB->quote($v);
			$set = implode(",\n\t", $set);
		}
		if(is_array($where)) {
			foreach($where as $k=>&$v)
				$v = "{$k} = " . $DB->quote($v);
			$where = implode("\n\tAND ", $where);
		}
		$tableName = static::TABLE_NAME;
		$sql = "UPDATE {$tableName}\n\tSET {$set}\n\tWHERE {$where}";
		if($format)
			$sql = sprintf($format, $sql);
		return $this->prepare($sql);
	}

	/**
	 * @param $sql
	 * @return \PDOStatement
	 */
	function prepare($sql) {
		$DB = $this->getDatabase();
		try {
			$statement = $DB->prepare($sql);

		} catch (\PDOException $ex) {
			$statement = null;
			if (stripos($ex->getMessage(), 'Duplicate') !== false)
				throw new RequestException($ex->getMessage(), null, $ex);
			$Schema = $DB;
			if($Schema instanceof IReadableSchema) {
				$TableWriter = new PDOTableWriter($DB);
				$TableWriter->tryRepair($ex, $Schema);
				$statement = $DB->prepare($sql);
				$ex = null;
			}

			if($ex)
				throw new \PDOException($ex->getMessage() . ' - ' . $sql, intval($ex->getCode()), $ex);
		}
		return $statement;
	}

	function fetch($whereColumn, $whereValue = null, $compare = '=?', $selectColumns = null) {
		return $this->select($selectColumns)
			->where($whereColumn, $whereValue, $compare, 2)
			->fetch();
	}

	function fetchOne($whereColumn, $whereValue = null, $compare = '=?', $selectColumns = null) {
		return $this->select($selectColumns)
			->where($whereColumn, $whereValue, $compare, 2)
			->fetchOne();
	}

	public function fetchColumn($column_number, $search, $searchColumn = null, $compare = '=?', $selectColumns = null) {
		return $this->select($selectColumns)
			->where($searchColumn, $search, $compare, 2)
			->fetchColumn($column_number);
	}

	function fetchAll($whereColumn, $whereValue = null, $compare = '=?', $limit = null, $selectColumns = null) {
		return $this->select($selectColumns)
			->where($whereColumn, $whereValue, $compare, $limit)
			->fetchAll();
	}

	function where($whereColumn, $whereValue = null, $compare = '=?', $logic = 'AND') {
		$Where = new PDOSelectBuilder($this->getDatabase());
		$Where->table(static::TABLE_NAME);
		$Where->setFetchMode(static::FETCH_MODE, static::FETCH_CLASS);
		$Where->where($whereColumn, $whereValue, $compare, $logic);
		return $Where;
	}

	function select($column=null, $alias=null, $format=null) {
		$Select = new PDOSelectBuilder($this->getDatabase());
		$Select->table(static::TABLE_NAME);
		if($column === null) {
			$Select->setFetchMode(static::FETCH_MODE, static::FETCH_CLASS);
			$Select->select(static::SELECT_COLUMNS);

		} else {
			$Select->setFetchMode(\PDO::FETCH_ASSOC);
			$Select->select($column, $alias, $format);
		}

		return $Select;
	}

	function update($fieldName, $fieldValue=null, $set='=?') {
		$Update = new PDOUpdateBuilder($this->getDatabase());
		$Update->table(static::TABLE_NAME);
		$Update->update($fieldName, $fieldValue, $set);
		return $Update;
	}

	function insert($fields, $_field=null) {
		$Insert = new PDOInsertBuilder($this->getDatabase());
		$Insert->table(static::TABLE_NAME);
		$Insert->insert(is_array($fields) ? $fields : func_get_args());
		return $Insert;
	}

	function execInsert(Array $insertData, IRequest $Request=null) {
		$this->insert($insertData)
			->execute($Request);
		return $this;
	}

	function delete($whereColumn=null, $whereValue = null, $compare = '=?', $logic = 'AND') {
		$Delete = new PDODeleteBuilder($this->getDatabase());
		$Delete->table(static::TABLE_NAME);
        if($whereColumn !== null)
		    $Delete->where($whereColumn, $whereValue, $compare, $logic);
		return $Delete;
	}

	function writeSchemaToPDODatabase(\PDO $PDO=null) {
		$TableSchema = new TableSchema(get_called_class());
		$TableWriter = new PDOTableWriter($PDO);
		$TableSchema->writeSchema($TableWriter);
	}

	/**
	 * Map sequential data to the map
	 * @param ISequenceMapper $Map
	 * @param int $offset
	 * @param int $limit
	 */
	function mapSequence(ISequenceMapper $Map, $offset=0, $limit=100) {
		$this->select()->mapSequence($Map, $offset, $limit);
	}

	/**
	 * Write schema to a writable source
	 * @param IWritableSchema $DB
	 */
	function writeSchema(IWritableSchema $DB) {
		$Schema = $this->getSchema();
		$Schema->writeSchema($DB);
	}

	/**
	 * Add a log entry
	 * @param mixed $msg The log message
	 * @param int $flags [optional] log flags
	 * @return int the number of listeners that processed the log entry
	 */
	function log($msg, $flags = 0) {
		$c = 0;
		foreach($this->mLogListeners as $Log)
			$c += $Log->log($msg, $flags);
		return $c;
	}

	/**
	 * Add a log listener callback
	 * @param ILogListener $Listener
	 * @return void
	 */
	function addLogListener(ILogListener $Listener) {
		if(!in_array($Listener, $this->mLogListeners))
		$this->mLogListeners[] = $Listener;
	}
}

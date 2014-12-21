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
use CPath\Request\Log\ILogListener;

define('AbstractPDOTable', __NAMESPACE__ . '\\AbstractPDOTable');
abstract class AbstractPDOTable implements ISequenceMap, IReadableSchema, ILogListener
{
	const className = AbstractPDOTable;
	const SELECT_COLUMNS = '*';
	const INSERT_COLUMNS = null;
	const UPDATE_COLUMNS = null;

	const TABLE_NAME = null;
	const FETCH_CLASS = null;

	const DEFAULT_LIMIT = 25;

	const PRIMARY_COLUMN = null;

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

	function getSelectStatement($selectColumns = '*', $searchColumn = null, $compare = '=?', $limit = null, $format = null) {
		$selectColumns === '*' ? $selectColumns = self::SELECT_COLUMNS : null;
		$tableName = static::TABLE_NAME;
		is_string($limit) ?: $limit = " LIMIT " . ($limit ?: static::DEFAULT_LIMIT);
		$sql = "SELECT {$selectColumns}\n FROM {$tableName}\n";
		$sql .= $this->getWhereSQL($searchColumn, $compare);
		if($format)
			$sql = sprintf($format, $sql);
		return $this->prepare($sql);
	}

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

	function getWhereSQL($searchColumn = null, $compare = '=?') {
		$searchColumn!==null ?: $searchColumn = static::PRIMARY_COLUMN;
		$sql = "WHERE {$searchColumn}{$compare}";
		return $sql;
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
			return $DB->prepare($sql);
		} catch (\PDOException $ex) {
			if(preg_match('/no such table: (.*)$/i', $ex->getMessage(), $matches)) {
				$tableName = $matches[1];
				if($tableName === static::TABLE_NAME) {
					$TableWriter = new PDOTableWriter($DB);
					$this->writeSchema($TableWriter);
					$TableWriter->commit();
					return $DB->prepare($sql);
				}
			}

			throw new \PDOException($ex->getMessage() . ' - ' . $sql, intval($ex->getCode()), $ex);
		}
	}

	function fetch($search, $searchColumn = null, $compare = '=?', $selectColumns = '*') {
		$statement = $this->select($selectColumns, $search, $searchColumn, $compare, 1);
		return $statement->fetch();
	}

	function fetchOne($search, $searchColumn = null, $compare = '=?', $selectColumns = '*') {
		$result = $this->fetch($search, $searchColumn, $compare, $selectColumns);
		if(!$result)
			throw new \PDOException("Results not found: " . $search);
		return $result;
	}

	function fetchAll($search, $searchColumn = null, $compare = '=?', $limit = null, $selectColumns = '*') {
		$statement = $this->select($selectColumns, $search, $searchColumn, $compare, $limit);
		return $statement->fetchAll();
	}

	function select($selectColumns = '*', $search = null, $searchColumn = null, $compare = '=?', $limit = null, $format=null) {
		$statement = $this->getSelectStatement($selectColumns, $searchColumn, $compare, $limit, $format);
		$statement->execute(array($search));
		$statement->setFetchMode(\PDO::FETCH_CLASS, static::FETCH_CLASS);
		return $statement;
	}

	function update($set, $where) {
		$statement = $this->getUpdateStatement($set, $where);
		return $statement->execute();
	}

	function insert($insert) {
		$statement = $this->getInsertStatement($insert);
		return $statement->execute();
	}

// TODO
//	function insertAndFetch($insert) {
//	}

	function updateByPrimary($primaryKeyID, $set) {
		$DB = $this->getDatabase();

		return $this->update($set, static::PRIMARY_COLUMN . ' = ' . $DB->quote($primaryKeyID));
	}

	function writeSchemaToPDODatabase(\PDO $PDO=null) {
		$TableSchema = new TableSchema(get_called_class());
		$TableWriter = new PDOTableWriter($PDO);
		$TableSchema->writeSchema($TableWriter);
	}

	static function cls() { return get_called_class(); }

	/**
	 * Map sequential data to the map
	 * @param ISequenceMapper $Map
	 * @param int $offset
	 * @param int $limit
	 */
	function mapSequence(ISequenceMapper $Map, $offset=0, $limit=100) {
		$limit = "LIMIT {$offset} {$limit}";
		$statement = $this->select(null, null, null, null, $limit);
		$i = $offset;
		while($row = $statement->fetch()) {
			$ret = $Map->mapNext($row, $i++);
			if($ret === true)
				break;
		}
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
		$this->mLogListeners[] = $Listener;
	}
}

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

abstract class AbstractPDOTable implements ISequenceMap, IReadableSchema
{
	const TABLE_NAME = null;
	const ROW_CLASS = null;

	const DEFAULT_LIMIT = 25;

	const PRIMARY_COLUMN = null;
	const SELECT_COLUMNS = '*';
	const SELECT_COMPARE = '=?';

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

	function getSelectSQL($searchColumn = null, $compare = null, $limit = null, $format=null) {
		$tableName = static::TABLE_NAME;
		$selectColumns = static::SELECT_COLUMNS;
		is_string($limit) ?: $limit = " LIMIT " . ($limit ?: static::DEFAULT_LIMIT);
		$sql = "SELECT {$selectColumns} FROM {$tableName}";
		$sql .= $this->getWhereSQL($searchColumn, $compare);
		if($format)
			$sql = sprintf($format, $sql);
		return $sql;
	}

	function getWhereSQL($searchColumn = null, $compare = null) {
		$searchColumn!==null ?: $searchColumn = static::PRIMARY_COLUMN;
		$compare!==null ?: $compare = static::SELECT_COMPARE;
		$sql = "WHERE {$searchColumn} {$compare}";
		return $sql;
	}


	function getUpdateSQL($set, $where, $format=null) {
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
		$sql = "UPDATE {$tableName} SET {$set} WHERE {$where}";
		if($format)
			$sql = sprintf($format, $sql);
		return $sql;
	}

	function fetch($search = null, $searchColumn = null, $compare = null, $limit = null, $format=null) {
		$query = $this->select($search, $searchColumn, $compare, $limit, $format);
		return $query->fetch();
	}

	function fetchOne($search = null, $searchColumn = null, $compare = null, $limit = null, $format=null) {
		$result = $this->fetch($search, $searchColumn, $compare, $limit, $format);
		if(!$result)
			throw new \PDOException("Row not found: " . $search);
		return $result;
	}

	function fetchAll($search = null, $searchColumn = null, $compare = null, $limit = null, $format=null) {
		$query = $this->select($search, $searchColumn, $compare, $limit, $format);
		return $query->fetchAll();
	}

	function select($search = null, $searchColumn = null, $compare = null, $limit = null, $format=null) {
		$DB = $this->getDatabase();

		$sql = $this->getSelectSQL($searchColumn, $compare, $limit, $format);
		$query = $DB->prepare($sql);
		$query->setAttribute(\PDO::FETCH_CLASS, get_called_class());
		$query->execute(array($search));

		return $query;
	}

	function update($set, $where) {
		$DB = $this->getDatabase();

		$sql = $this->getUpdateSQL($set, $where);
		$query = $DB->prepare($sql);
		return $query->execute();
	}

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
		$query = $this->select(null, null, null, null, $limit);
		$i = $offset;
		while($row = $query->fetch()) {
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
}
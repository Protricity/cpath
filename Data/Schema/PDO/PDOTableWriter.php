<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/19/2014
 * Time: 4:16 PM
 */
namespace CPath\Data\Schema\PDO;

use CPath\Data\Schema\IReadableSchema;
use CPath\Data\Schema\IWritableSchema;


class PDOTableWriter implements IWritableSchema
{

	private $mPDO;
	private $mTableName;
	private $mTableInfo = null;
	private $mColumns = array();
	private $mIndexes = array();

	public function __construct(\PDO $PDO) {
		$this->mPDO = $PDO;
	}

	public function tryRepair(\PDOException $ex, IReadableSchema $Schema) {
		$matched = preg_match('/no such (table|column): ([\w_-]+)/i', $ex->getMessage(), $matches)
			|| preg_match('/(table|column) ([\w_-]+) has no column named ([\w_-]+)/i', $ex->getMessage(), $matches);

		if($matched) {
			list(, $type, $name) = $matches;
			switch($type) {
				case 'table':
					//$this->mTableName = $name;
					break;
				case 'column':
					break;
				default:
					throw new \Exception("Invalid type: " . $type);
			}
			$Schema->writeSchema($this);
			$this->commit();
			return true;
		}
		return false;
	}

	public function commit() {
		if ($this->mTableInfo !== null) {
			list($Schema, $tableName, $tableArgs, $tableComment) = $this->mTableInfo;
			$this->mTableInfo = null;

			// write
			$DB = $this->mPDO;

			$sql  = "CREATE TABLE {$tableName}"
				. "(";

			foreach($this->mColumns as $i => $columnInfo) {
				list($columnName, $columnArgs, $columnComment) = $columnInfo;
				$sql .= ($i ? "," : '') . "\n\t{$columnName} {$columnArgs}";
			}

			$sql .= "\n) {$tableArgs};";

			try {
				$DB->exec($sql);
			} catch (\PDOException $ex) {
				if(strpos($ex->getMessage(), "table {$tableName} already exists") !== false) {

				} else {
					throw new \PDOException($ex->getMessage() . ' - ' . $sql, null, $ex);
				}
			}

			foreach($this->mColumns as $columnInfo) {
				list($columnName, $columnArgs, $columnComment) = $columnInfo;
				$sql = "ALTER TABLE {$tableName} ADD COLUMN {$columnName} {$columnArgs}";
				try {
					$DB->exec($sql);
				} catch (\PDOException $ex) {
					if(strpos($ex->getMessage(), 'duplicate column name: ' . $columnName) !== false) {

					} else {
						throw new \PDOException($ex->getMessage() . ' - ' . $sql, null, $ex);
					}
				}
			}

			foreach($this->mIndexes as $indexInfo) {
				list($indexName, $columns, $indexArgs, $indexComment) = $indexInfo;
				$sql = "CREATE {$indexArgs} INDEX {$indexName} ON {$tableName} ({$columns})";
				try {
					$DB->exec($sql);
				} catch (\PDOException $ex) {
					if(strpos($ex->getMessage(), $indexName . ' already exists') !== false) {

					} else {
						throw new \PDOException($ex->getMessage() . ' - ' . $sql, null, $ex);
					}
				}
			}
		}
		$this->mTableInfo = null;
		$this->mColumns   = array();
		$this->mIndexes   = array();
	}

	/**
	 * Create a table in the schema
	 * @param IReadableSchema $Schema
	 * @param String $tableName
	 * @param String|null $tableArgs
	 * @param String|null $tableComment
	 * @return void
	 */
	function writeTable(IReadableSchema $Schema, $tableName, $tableArgs = null, $tableComment = null) {
		$this->commit();
		$this->mColumns   = array();
		$this->mIndexes   = array();
		$this->mTableInfo = null;
		if($this->mTableName && $this->mTableName !== $tableName) {
			return;
		}
		$this->mTableInfo = func_get_args();
	}

	/**
	 * Write a column to the last schema table
	 * @param IReadableSchema $Schema
	 * @param String $columnName
	 * @param String|null $columnArgs
	 * @param String|null $columnComment
	 * @return void
	 */
	function writeColumn(IReadableSchema $Schema, $columnName, $columnArgs = null, $columnComment = null) {
		if($this->mTableInfo)
			$this->mColumns[] = array_slice(func_get_args(), 1);
	}

	/**
	 * Write a column index to the last schema table
	 * @param IReadableSchema $Schema
	 * @param $indexName
	 * @param String $columns list of columns comma delimited
	 * @param String|null $indexArgs
	 * @param String|null $indexComment
	 * @return mixed
	 */
	function writeIndex(IReadableSchema $Schema, $indexName, $columns, $indexArgs = null, $indexComment = null) {
		if($this->mTableInfo)
			$this->mIndexes[] = array_slice(func_get_args(), 1);
	}
}
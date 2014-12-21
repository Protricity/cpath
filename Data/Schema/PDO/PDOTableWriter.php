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
	private $mTableInfo = null;
	private $mColumns = array();
	private $mIndexes = array();

	public function __construct(\PDO $PDO) {
		$this->mPDO = $PDO;
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

			$sql .= ") {$tableArgs};";

			$DB->exec($sql);

			foreach($this->mColumns as $columnInfo) {
				list($columnName, $columnArgs, $columnComment) = $columnInfo;
				$sql = "ALTER TABLE {$tableName} ADD COLUMN {$columnName} {$columnArgs}";
				try {
					$DB->exec($sql);
				} catch (\PDOException $ex) {
					if(strpos($ex->getMessage(), 'duplicate column name: ' . $columnName) !== false) {

					} else {
						throw $ex;
					}
				}
			}

			foreach($this->mIndexes as $indexInfo) {
				list($indexName, $columns, $indexArgs, $indexComment) = $indexInfo;
				$sql = "CREATE INDEX {$indexName} {$indexArgs} ON {$tableName} ({$columns})";
				try {
					$DB->exec($sql);
				} catch (\PDOException $ex) {
					if(strpos($ex->getMessage(), 'duplicate column name: ' . $columnName) !== false) {

					} else {
						throw $ex;
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
		$this->mIndexes[] = array_slice(func_get_args(), 1);
	}
}
<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/21/2014
 * Time: 9:00 PM
 */
namespace CPath\Data\Schema\PDO;


use CPath\Data\Schema\IReadableSchema;
use CPath\Request\Exceptions\RequestException;

abstract class AbstractPDOQueryBuilder
{
	private $mPDO;
	private $mValues = array();
	private $mModes = array();
	private $mPrepared = null;
	private $mExecuted = false;

	protected $mValueIndex = 1;
	protected $mTableSQL = null;
	protected $mFormat = null;

	abstract function getSQL();

	public function __construct(\PDO $PDO) {
		$this->mPDO = $PDO;
	}

	public function __destruct() {
		if($this->mPrepared === null) {
			$r = $this->execute();
		}
	}

	public function getDatabase() {
		return $this->mPDO;
	}

	public function table($table, $alias = null) {
		if ($alias !== null) {
			$table .= " as " . $alias;
		}

		$this->mTableSQL = $table;

		return $this;
	}

	public function setFetchMode($mode) {
		$this->mModes[] = func_get_args();

		return $this;
	}

	public function bindValue($value, $name=null) {
		if($name === null)
			$this->mValues[$this->mValueIndex++] = $value;
		else
			$this->mValues[$name] = $value;

		return $this;
	}

	public function prepare() {
		if($this->mPrepared)
			return $this->mPrepared;

		$DB = $this->getDatabase();
		$sql       = $this->getSQL();

		try {
			$statement = $DB->prepare($sql);

		} catch (\PDOException $ex) {
			$statement = null;
			if (stripos($ex->getMessage(), 'Duplicate') !== false)
				throw new DuplicateRowException($ex->getMessage(), null, $ex);

			$Schema = $DB;
			if($Schema instanceof IReadableSchema) {
				$TableWriter = new PDOTableWriter($DB);
				$TableWriter->tryRepair($ex, $Schema);
				$statement = $this->mPDO->prepare($sql);
				$ex = null;
			}

			if($ex)
				throw new \PDOException($ex->getMessage() . ' - ' . $sql, intval($ex->getCode()), $ex);
		}

		foreach ($this->mModes as $mode)
			$statement->setFetchMode($mode[0], isset($mode[1]) ? $mode[1] : null, isset($mode[2]) ? $mode[2] : null);
		foreach ($this->mValues as $k => $v)
			$statement->bindValue($k, $v);

		return $this->mPrepared = $statement;
	}

	public function execute(Array $values = null) {
		$statement = $this->prepare();
		if(!$this->mExecuted) try {
			$this->mExecuted = $statement->execute($values);
		} catch (\PDOException $ex) {
			if (preg_match('/column (.*) is not unique/i', $ex->getMessage(), $matches))
				throw new DuplicateRowException($matches[1], $ex->getMessage(), null, $ex);
			throw $ex;
		}
//		if($this->getDatabase()->errorInfo()
		return $this->mExecuted;
	}

	public function format($format) {
		$this->mFormat = $format;
	}
}
